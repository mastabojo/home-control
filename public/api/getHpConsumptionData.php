<?php
$baseDir = dirname(__DIR__, 2);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

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

if(php_sapi_name() == 'cli') {
    $date = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : date('Y-m-d');
} else {
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
}

/*
 * Daily consumption data
 */
$month = date('m', strtotime($date));
$previuousMonth = date('n', strtotime($date)) > 1 ? str_pad(date('n', strtotime($date)) - 1, 2, '0', STR_PAD_LEFT)  : '12';
$year = date('Y', strtotime($date));
$previousMonthsYear = date('n', strtotime($date)) > 1 ? date('Y', strtotime($date)) : date('Y', strtotime($date)) - 1;
$daysInMonth = date('t', strtotime($date));

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

// Fill in missing values
$hourlyDataTotals = fillMissingKeys($hourlyDataTotals, 24, $total_energy_prev);

// Get differences
$hourlyDataDiffs = arrayGetDiffs($hourlyDataTotals, $total_energy_prev);

// Consumption for all tariffs
$count = count($hourlyDataTotals);
$total = $count > 0 ? (max($hourlyDataTotals) - min($hourlyDataTotals)) : 0;
if($singleTarrif) {
    $singleTariff = $total;
    $lowTariff = 0;
    $highTariff = 0;
} else {
    $singleTariff = 0;
    // Empty array (between 0:00 and first reading)
    if($count == 0) {
        $lowTariff = 0;
        $highTariff = 0;
    // Low tariff morning part only on workdays or low tariff all day on non-work days)
    } elseif(!isWorkDay($date) || $count > 0 && ($count < $ELECTRIC_POWER_HIGH_TARIFF_START)) {
        $lowTariff = $total;
        $highTariff = 0;
    // Low tariff morning part and high tariff but no low tariff evening part, work days only 
    } elseif(isWorkDay($date) && $count >= $ELECTRIC_POWER_HIGH_TARIFF_START && $count < $ELECTRIC_POWER_HIGH_TARIFF_END) {
        $lowTariffRows = array_slice($hourlyDataTotals, 0, $ELECTRIC_POWER_HIGH_TARIFF_START - 1);
        $lowTariff = max($lowTariffRows) - min($lowTariffRows);
        $highTariff = $total - $lowTariff;
    // Low tariff morning part and high tariff and low tariff evening part, work days only 
    } else {
        $highTariffRows = array_slice($hourlyDataTotals, $ELECTRIC_POWER_HIGH_TARIFF_START, ($ELECTRIC_POWER_HIGH_TARIFF_END - $ELECTRIC_POWER_HIGH_TARIFF_START));
         $highTariff = max($highTariffRows) - min($highTariffRows);
        $lowTariff = $total - $highTariff;
    }
}

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
         WHERE MONTH(read_time) = $previuousMonth AND YEAR(read_time) = $previousMonthsYear AND tariff='$LT'";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $total_monthly_prev_lt = $stmt->fetchColumn(0);
    
    // Get previous month max value for high tariff
    $q = "SELECT MAX(total_energy) AS previous_ht FROM heat_pump_KWh
    WHERE MONTH(read_time) = $previuousMonth AND YEAR(read_time) = $previousMonthsYear AND tariff='$HT'";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $total_monthly_prev_ht = $stmt->fetchColumn(0);

    // Get daily values for selected month for low tariff
    $q = "SELECT DAY(read_time) as read_time, MAX(total_energy) AS max_daily FROM `heat_pump_KWh` 
        WHERE MONTH(read_time) = $month AND YEAR(read_time) = $year AND tariff = '$LT'
        GROUP BY DAY(read_time)";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $rows_monthly_consumption_lt = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $dailyDataTotalsLt = fillMissingKeys($rows_monthly_consumption_lt, $daysInMonth, $total_monthly_prev_lt);
    $dailyDataDiffsLt = arrayGetDiffs($dailyDataTotalsLt, $total_monthly_prev_lt);

    // Get daily values for selected month for high tariff
    $q = "SELECT DAY(read_time) as read_time, MAX(total_energy) AS max_daily FROM `heat_pump_KWh` 
        WHERE MONTH(read_time) = $month AND YEAR(read_time) = $year AND tariff = '$HT'
        GROUP BY DAY(read_time)";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $rows_monthly_consumption_ht = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $dailyDataTotalsHt = fillMissingKeys($rows_monthly_consumption_ht, $daysInMonth, $total_monthly_prev_ht);
    $dailyDataDiffsHt = arrayGetDiffs($dailyDataTotalsHt, $total_monthly_prev_ht);
}

// 
$q = "SELECT tariff, ROUND(MAX(total_energy) - MIN(total_energy), 2) AS monthly FROM `heat_pump_KWh` 
WHERE MONTH(read_time) = MONTH('$date') AND YEAR(read_time) = YEAR('$date')
GROUP BY tariff";
$stmt = $DB->prepare($q);
$stmt->execute();
$rows_monthly_total = $stmt->fetchAll(PDO::FETCH_ASSOC);


$singleTariffM = 0;
$lowTariffM = 0;
$highTariffM = 0;
foreach($rows_monthly_total as $key => $val) {
    switch($val['tariff']) {
        case $ST: $singleTariffM = isset($val['monthly']) ? $val['monthly'] : 0; break;
        case $LT: $lowTariffM = isset($val['monthly']) ? $val['monthly'] : 0; break;
        case $HT: $highTariffM = isset($val['monthly']) ? $val['monthly'] : 0; break;
    }
}

$totalM = isset($singleTariffM) && $singleTariffM > 0 ? $singleTariffM : ($highTariffM + $lowTariffM);

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
