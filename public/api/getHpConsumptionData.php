<?php
$baseDir = dirname(__DIR__, 2);
include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

/* 
 * Ceck for parameters
 * Either both parameters or none shall be supplied 
 * Param 1: display date (what date are we interested in - in mysql format yyyy-mm-dd)
 * Param 2: display period (what period is displayed - daily, monthly, yearly)
 */
$allowedPeriods = ['daily', 'monthly', 'yearly'];
$allowedDateFormat = '/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$/';
if(
    php_sapi_name() == 'cli' && 
    isset($argv[1]) && 
    isset($argv[2]) && 
    preg_match($allowedDateFormat, $argv[1]) === 1 && 
    in_array($argv[2], $allowedPeriods)) {
        $date = $argv[1];
        $period =  $argv[2];
    } 
elseif(
    php_sapi_name() != 'cli' && 
    isset($_POST['dispDate']) && 
    isset($_POST['dispPeriod']) && 
    preg_match($allowedDateFormat, $_POST['dispDate']) === 1 &&
    in_array($_POST['dispPeriod'], $allowedPeriods)) {
        $date = $_POST['dispDate'];
        $period =  $_POST['dispPeriod'];
    }
else {
    $date = date("Y-m-d");
    $period = 'daily';
}

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

$singleTarrif = isset($ELECTRIC_POWER_SINGLE_TARIFF) ? $ELECTRIC_POWER_SINGLE_TARIFF : true;
$singleRate = isset($ELECTRIC_POWER_SINGLE_RATE) ? $ELECTRIC_POWER_SINGLE_RATE : 0.07069;
$lowRate = isset($ELECTRIC_POWER_LOW_RATE) ? $ELECTRIC_POWER_LOW_RATE : 0.04391;
$highRate = isset($ELECTRIC_POWER_HIGH_RATE) ? $ELECTRIC_POWER_HIGH_RATE : 0.07918;
$highTariffStart = isset($ELECTRIC_POWER_HIGH_TARIFF_START) ? $ELECTRIC_POWER_HIGH_TARIFF_START : 6;
$highTariffEnd = isset($ELECTRIC_POWER_HIGH_TARIFF_END) ? $ELECTRIC_POWER_HIGH_TARIFF_END : 22;

// Tariff abbreviations
$ST = 'et';
$LT = 'mt';
$HT = 'vt';

$month = date('m', strtotime($date));
$previuousMonth = date('n', strtotime($date)) > 1 ? str_pad(date('n', strtotime($date)) - 1, 2, '0', STR_PAD_LEFT)  : '12';
$year = date('Y', strtotime($date));
$previousMonthsYear = date('n', strtotime($date)) > 1 ? date('Y', strtotime($date)) : date('Y', strtotime($date)) - 1;
$daysInMonth = date('t', strtotime($date));

/*
 * Daily consumption data
 */

 // get last tariff from previous day
$q = "SELECT ROUND(MAX(total_energy), 2) AS read_energy 
FROM heat_pump_KWh WHERE read_time < '$date';";
$stmt = $DB->prepare($q);
$stmt->execute();
$total_energy_prev = $stmt->fetchColumn(0);

 // Get all tariff readings for current day from database
$q = "SELECT HOUR(read_time) AS read_time, ROUND(MAX(total_energy), 2) AS read_value 
FROM heat_pump_KWh WHERE read_time LIKE CONCAT('$date', '%') GROUP BY HOUR(read_time);";
$stmt = $DB->prepare($q);
$stmt->execute();
// $rows_all_tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hourlyDataTotals = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE);

// How many values there are for selected day
$count = count($hourlyDataTotals);

// Fill in missing values
$hourlyDataTotals = fillMissingKeys($hourlyDataTotals, 24, $total_energy_prev);

// Get differences
$hourlyDataDiffs = arrayGetDiffs($hourlyDataTotals, $total_energy_prev);

// Consumption for all tariffs
$total = $lowTariff = array_sum($hourlyDataDiffs);

if($singleTarrif) {
    $singleTariff = $total;
    $lowTariff = 0;
    $highTariff = 0;
} else {
    $singleTariff = 0;
    // Non working days - only low tariff
    if(!isWorkDay($date)) {
        $lowTariff = array_sum($hourlyDataDiffs);
        $highTariff = 0;
    } elseif($count < $ELECTRIC_POWER_HIGH_TARIFF_START) {
        $lowTariff = array_sum($hourlyDataDiffs);
        $highTariff = 0;
    } elseif($count >= $ELECTRIC_POWER_HIGH_TARIFF_START && $count < $ELECTRIC_POWER_HIGH_TARIFF_END) {
        $lowTariffRows = array_slice($hourlyDataDiffs, 0, $ELECTRIC_POWER_HIGH_TARIFF_START - 1);
        $lowTariff = array_sum($lowTariffRows);
        $highTariff = $total - $lowTariff;
    } else {
        $highTariffRows = array_slice($hourlyDataDiffs, $ELECTRIC_POWER_HIGH_TARIFF_START, ($ELECTRIC_POWER_HIGH_TARIFF_END - $ELECTRIC_POWER_HIGH_TARIFF_START));
        $highTariff = array_sum($highTariffRows);
        $lowTariff = $total - $highTariff;
    }
}

// Daily consumption data for preparing JSON
$dailyConsumption = [
    'singleTariff' => round($singleTariff, 2), 
    'lowTariff' => round($lowTariff, 2), 
    'highTariff' => round($highTariff, 2),
    'total' => round($total, 2),
    'singleTariffCost' => round(($singleTariff * $singleRate), 2),
    'lowTariffCost' => round(($lowTariff * $lowRate), 2),
    'highTariffCost' => round(($highTariff * $highRate), 2),
    'totalCost' => round((($lowTariff * $lowRate) + ($highTariff * $highRate)), 2)
];

/*
 * Monthly consumption data
 */

// Get max readings for each day of the month for low and high tariffs or single tariff
if($singleTarrif) {

    // Get previous month max value for single tariff
    $total_monthly_prev_st = "SELECT ROUND(MAX(total_energy), 2) AS previous_st FROM heat_pump_KWh 
        WHERE MONTH(read_time) = $previuousMonth AND YEAR(read_time) = $previousMonthsYear AND tariff='$ST'";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $total_monthly_prev_st = $stmt->fetchColumn(0);
    
    // Get daily readings for single tariff
    $q = "SELECT DAY(read_time) as read_time, MAX(total_energy) AS max_daily FROM `heat_pump_KWh` 
        WHERE MONTH(read_time) = $month AND YEAR(read_time) = $year AND tariff = '$ST'
        GROUP BY DAY(read_time)";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $rows_monthly_consumption_st = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE);
    
    $dailyDataTotalsSt = fillMissingKeys($rows_monthly_consumption_st, $daysInMonth, $total_monthly_prev_st);
    $dailyDataDiffsSt = arrayGetDiffs($rows_monthly_consumption_st, $total_monthly_prev_st);

} else {

    // Get previous month max value for low tariff
    $q = "SELECT MAX(total_energy) AS previous_lt FROM heat_pump_KWh
         WHERE MONTH(read_time)='$previuousMonth' AND YEAR(read_time)='$previousMonthsYear' AND tariff='$LT'";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $total_monthly_prev_lt = $stmt->fetchColumn(0);

    // Get previous month max value for high tariff
    $q = "SELECT MAX(total_energy) AS previous_ht FROM heat_pump_KWh
    WHERE MONTH(read_time)='$previuousMonth' AND YEAR(read_time)='$previousMonthsYear' AND tariff='$HT'";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $total_monthly_prev_ht = $stmt->fetchColumn(0);

    // Get daily values for selected month for low tariff
    $q = "SELECT DAY(read_time) as read_time, MAX(total_energy) AS max_daily FROM `heat_pump_KWh` 
        WHERE MONTH(read_time)='$month' AND YEAR(read_time)='$year' AND tariff='$LT'
        GROUP BY DAY(read_time)";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $rows_monthly_consumption_lt = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE);

    $dailyDataTotalsLt = fillMissingKeys($rows_monthly_consumption_lt, $daysInMonth, $total_monthly_prev_lt);
    $dailyDataDiffsLt = arrayGetDiffs($dailyDataTotalsLt, $total_monthly_prev_lt);

    // Get daily values for selected month for high tariff
    $q = "SELECT DAY(read_time) as read_time, MAX(total_energy) AS max_daily FROM `heat_pump_KWh` 
        WHERE MONTH(read_time) = '$month' AND YEAR(read_time)='$year' AND tariff='$HT'
        GROUP BY DAY(read_time)";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $rows_monthly_consumption_ht = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE);

    $dailyDataTotalsHt = fillMissingKeys($rows_monthly_consumption_ht, $daysInMonth, $total_monthly_prev_ht);
    $dailyDataDiffsHt = arrayGetDiffs($dailyDataTotalsHt, $total_monthly_prev_ht);
}

// 
$q = "SELECT tariff, ROUND(MAX(total_energy) - MIN(total_energy), 2) AS monthly FROM `heat_pump_KWh` 
WHERE MONTH(read_time) = MONTH('$date') AND YEAR(read_time) = YEAR('$date')
GROUP BY tariff";
$stmt = $DB->prepare($q);
$stmt->execute();
// $rows_monthly_total = $stmt->fetchAll(PDO::FETCH_ASSOC);
$rows_monthly_total = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE);

if($singleTarrif) {
    $singleTariffM = isset($rows_monthly_total[$ST]) ? $rows_monthly_total[$ST] : 0;
    $lowTariffM = 0;
    $highTariffM = 0;
} else {
    $lowTariffM = isset($rows_monthly_total[$LT]) ? $rows_monthly_total[$LT] : 0;
    $highTariffM = isset($rows_monthly_total[$HT]) ? $rows_monthly_total[$HT] : 0;
    $singleTariffM = 0;
}

$totalM = $singleTarrif ? $singleTariffM : ($highTariffM + $lowTariffM);

$monthlyConsumption = [
    'singleTariff' => round($singleTariffM, 2), 
    'lowTariff' => round($lowTariffM, 2), 
    'highTariff' => round($highTariffM, 2),
    'total' => round($totalM, 2),
    'singleTariffCost' => round(($singleTariffM * $singleRate), 2),
    'lowTariffCost' => round(($lowTariffM * $lowRate), 2),
    'highTariffCost' => round(($highTariffM * $highRate), 2),
    'totalCost' => round((($lowTariffM * $lowRate) + ($highTariffM * $highRate)), 2)
];

// return JSON encoded data
echo json_encode([
    'tariff' => $singleTarrif ? 'single_tariff' : 'dual_tariff',
    'high_tariff_boundaries' => [$highTariffStart, $highTariffEnd],
    'daily_consumption' => $dailyConsumption,
    'hourly_data' => $hourlyDataTotals,
    'hourly_data_diffs' => $hourlyDataDiffs,
    'monthly_consumption' => $monthlyConsumption,
    'daily_data_lt' => $dailyDataTotalsLt,
    'daily_data_ht' => $dailyDataTotalsHt,
    'daily_data_diffs_lt' => $dailyDataDiffsLt,
    'daily_data_diffs_ht' => $dailyDataDiffsHt,
    'rates' => ['low_rate' => $lowRate, 'high_rate' => $highRate, 'single_rate' => $singleRate]
], JSON_NUMERIC_CHECK);
