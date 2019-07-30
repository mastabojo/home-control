<?php

$baseDir = dirname(__DIR__, 2);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

// Non working holidays (Slovenia)
$nonWorkingHolidays = ['01-01', '01-02', '02-08', '04-27', '05-01', '05-02', '06-25', '08-15', '10-31', '11-01', '12-25', '12-26'];

// Easter Mondays for next 2 years
$easterMondays = ['2020-04-13', '2021-04-05', '2022-04-18', '2023-04-10', '2024-04-01', '2025-04-21', '2026-04-06', '2027-03-29', '2028-04-17', '2029-04-02', 
'2030-04-22', '2031-04-14', '2032-03-29', '2033-04-18', '2034-04-10', '2035-03-26', '2036-04-14', '2037-04-06', '2038-04-26', '2039-04-11', '2040-04-02'];

// Is it work day (not Saturday or Sunday or a non working holiday)
$isWorkDay = (date("N") == 6 || date("N") == 7 || in_array(date("m-d"), $nonWorkingHolidays) || in_array(date("Y-m-d"), $easterMondays)) ? false : true;

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

$singleTarrif = isset($ELECTRIC_POWER_SINGLE_TARIFF) ? $ELECTRIC_POWER_SINGLE_TARIFF : true;
$singleRate = isset($ELECTRIC_POWER_SINGLE_RATE) ? $ELECTRIC_POWER_SINGLE_RATE : 0.07069;
$lowRate = isset($ELECTRIC_POWER_LOW_RATE) ? $ELECTRIC_POWER_LOW_RATE : 0.04391;
$highRate = isset($ELECTRIC_POWER_HIGH_RATE) ? $ELECTRIC_POWER_HIGH_RATE : 0.07918;
$highTariffStart = isset($ELECTRIC_POWER_HIGH_TARIFF_START) ? $ELECTRIC_POWER_HIGH_TARIFF_START : 6;
$highTariffEnd = isset($ELECTRIC_POWER_HIGH_TARIFF_END) ? $ELECTRIC_POWER_HIGH_TARIFF_END : 22;

/*
 * Daily consumption data
 */

// Get all tariff readings for current day from database
$q = "SELECT HOUR(read_time) AS read_hour, ROUND(MAX(total_energy), 2) AS read_energy 
FROM heat_pump_KWh WHERE read_time LIKE CONCAT(CURDATE(), '%') GROUP BY read_hour;";
$stmt = $DB->prepare($q);
$stmt->execute();
$rows_all_tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// get last tariff from previous day (in case we do not have first reading)
$q = "SELECT ROUND(MAX(total_energy), 2) AS read_energy 
FROM heat_pump_KWh WHERE read_time LIKE CONCAT(DATE_ADD(CURDATE(), INTERVAL -1 DAY), '%');";
$stmt = $DB->prepare($q);
$stmt->execute();
$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_energy_prev = $row[0]['read_energy'];

// Rearrange hourly readings into indexed array
$hourlyDataTotals = [];
$hourlyDataDiffs = [];
// To ensure correct sequence of indexes
$hourIndex = 0;
// Prepare hourly data (rearrange data and make up for possible missing hours)
foreach($rows_all_tariffs as $key => $hourly) {
    $read_hour = $hourly['read_hour'];
    if($read_hour == $hourIndex) {
        $hourlyDataTotals[$hourIndex] =  $hourly['read_energy'];
    } else {
        if($hourIndex == 0) {
            $hourlyDataTotals[$hourIndex] = $total_energy_prev;
        } else {
            $hourlyDataTotals[$hourIndex] = isset($rows_all_tariffs[$key - 1]) ? $rows_all_tariffs[$key - 1] : $total_energy_prev;
        }
    }
    $hourIndex++;
}

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
    } elseif(!$isWorkDay || $count > 0 && ($count < $ELECTRIC_POWER_HIGH_TARIFF_START)) {
        $lowTariff = $total;
        $highTariff = 0;
    // Low tariff morning part and high tariff but no low tariff evening part, work days only 
    } elseif($isWorkDay && $count >= $ELECTRIC_POWER_HIGH_TARIFF_START && $count < $ELECTRIC_POWER_HIGH_TARIFF_END) {
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

// Fill missing measurements with zeroes
if($count < 24) {
    for($h = $count; $h <= 23; $h++) {
        $hourlyDataTotals[$h] = 0;
    }
}

// Create an array of differencies in readings (for another variant of consumption chart)
$hourlyDataDiffs = [];
foreach($hourlyDataTotals as $hour => $reading) {
    if($reading > 0) {
        $hourlyDataDiffs[$hour] = $hour > 0 ? 
        round($hourlyDataTotals[($hour)] - $hourlyDataTotals[$hour - 1], 3) : 
        round($hourlyDataTotals[$hour] - $total_energy_prev, 3);
    } else {
        $hourlyDataDiffs[$hour] = 0;
    }
}

/*
 * Monthly consumption data
 */

// Get max readings for each day of the month
$q = "SELECT DAY(read_time) as read_day, MAX(total_energy) AS max_daily, tariff FROM `heat_pump_KWh` 
WHERE MONTH(read_time) = MONTH(CURRENT_DATE()) AND YEAR(read_time) = YEAR(CURRENT_DATE()) 
GROUP BY read_day, tariff";
$stmt = $DB->prepare($q);
$stmt->execute();
$rows_monthly_consumption = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Rearange data into array with elements for each day of the month
$dailyDataTotals = [];
for($d = 1; $d <= date("t"); $d++) {
    $dailyDataTotals[$d] = ['mt' => 0, 'vt' => 0];
}

foreach($rows_monthly_consumption as $dailyValues) {
    if($dailyValues['tariff'] == 'mt') {
        $dailyDataTotals[$dailyValues['read_day']]['mt'] = $dailyValues['max_daily'];
    }
    if($dailyValues['tariff'] == 'vt') {
        $dailyDataTotals[$dailyValues['read_day']]['vt'] = $dailyValues['max_daily'];
    }
}


// Calculate daily data diffs
// ...
// ...
$dailyDataDiffs = [];



$q = "SELECT tariff, ROUND(MAX(total_energy) - MIN(total_energy), 2) AS monthly FROM `heat_pump_KWh` 
WHERE MONTH(read_time) = MONTH(CURRENT_DATE()) AND YEAR(read_time) = YEAR(CURRENT_DATE())
GROUP BY tariff";
$stmt = $DB->prepare($q);
$stmt->execute();
$rows_monthly_total = $stmt->fetchAll(PDO::FETCH_ASSOC);
$singleTariffM = 0;
$lowTariffM = 0;
$highTariffM = 0;
foreach($rows_monthly_total as $key => $val) {
    switch($val['tariff']) {
        case 'et': $singleTariffM = isset($val['monthly']) ? $val['monthly'] : 0; break;
        case 'mt': $lowTariffM = isset($val['monthly']) ? $val['monthly'] : 0; break;
        case 'vt': $highTariffM = isset($val['monthly']) ? $val['monthly'] : 0; break;
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
    'daily_data' => $dailyDataTotals,
    'daily_data_diffs' => $dailyDataDiffs,
    'rates' => ['low_rate' => $lowRate, 'high_rate' => $highRate, 'single_rate' => $singleRate]
], JSON_NUMERIC_CHECK);
