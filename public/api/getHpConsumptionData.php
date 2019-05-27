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
$hourlyData = [];
// To ensure correct sequence of indexes
$hourIndex = 0;
// Prepare hourly data (rearrange data and make up for possible missing hours)
foreach($rows_all_tariffs as $key => $hourly) {
    $read_hour = $hourly['read_hour'];
    if($read_hour == $hourIndex) {
        $hourlyData[$hourIndex] =  $hourly['read_energy'];
    } else {
        if($hourIndex == 0) {
            $hourlyData[$hourIndex] = $total_energy_prev;
        } else {
            $hourlyData[$hourIndex] = isset($rows_all_tariffs[$key - 1]) ? $rows_all_tariffs[$key - 1] : $total_energy_prev;
        }
    }
    $hourIndex++;
}

// Non working holidays (Slovenia)
$nonWorkingHolidays = ['01-01', '01-02', '02-08', '04-27', '05-01', '05-02', '06-25', '08-15', '10-31', '11-01', '12-25', '12-26'];

// Easter Mondays for next 2 years
$easterMondays = ['2020-04-13', '2021-04-05', '2022-04-18', '2023-04-10', '2024-04-01', '2025-04-21', '2026-04-06', '2027-03-29', '2028-04-17', '2029-04-02', 
'2030-04-22', '2031-04-14', '2032-03-29', '2033-04-18', '2034-04-10', '2035-03-26', '2036-04-14', '2037-04-06', '2038-04-26', '2039-04-11', '2040-04-02'];

// Is it work day (not Saturday or Sunday or a non working holiday)
$isWorkDay = (date("N") == 6 || date("N") == 7 || in_array(date("m-d"), $nonWorkingHolidays) || in_array(date("Y-m-d"), $easterMondays)) ? false : true;

// Consumption for all teariffs
$count = count($hourlyData);
$total = $count > 0 ? (end($hourlyData) - $hourlyData[0]) : 0;
if($singleTarrif) {
    $singleTariff = $total;
    $lowTariff = 0;
    $highTariff = 0;
} else {
    $singleTariff = 0;
    if($count == 0) {
        $lowTariff = 0;
        $highTariff = 0;
    } elseif($count > 0 && ($count < $ELECTRIC_POWER_HIGH_TARIFF_START || !$isWorkDay)) {
        $lowTariff = $total;
        $highTariff = 0;
    } elseif($count >= $ELECTRIC_POWER_HIGH_TARIFF_START && $count < $ELECTRIC_POWER_HIGH_TARIFF_END && $isWorkDay) {
        $lowTariffRows = array_splice($hourlyData, 0, $ELECTRIC_POWER_HIGH_TARIFF_START - 1);
        $lowTariff = array_sum($lowTariffRows);
        $lowTariff = end($isWorkDay) - $lowTariffRows[0];
        $highTariff = $total - $lowTariff;
    } else {
        $highTariffRows = array_splice($hourlyData, $ELECTRIC_POWER_HIGH_TARIFF_START, ($ELECTRIC_POWER_HIGH_TARIFF_END - $ELECTRIC_POWER_HIGH_TARIFF_START));
        $highTariff = end($highTariffRows) - $highTariffRows[0];
        $lowTariff = $total - $highTariff;
    }
}
$consumption = [
    'singleTariff' => round($singleTariff, 2), 
    'lowTariff' => round($lowTariff, 2), 
    'highTariff' => round($highTariff, 2),
    'total' => round($total, 2),
    'singleTariffCost' => round(($singleTariff * $singleRate), 2),
    'lowTariffCost' => round(($lowTariff * $lowRate), 2),
    'highTariffCost' => round(($highTariff * $highRate), 2),
    'totalCost' => round((($lowTariff * $lowRate) + ($highTariff * $highRate)), 2)
];

DF($consumption);

// return JSON encoded data
echo json_encode([
    'tariff' => $singleTarrif ? 'single_tariff' : 'dual_tariff',
    'high_tariff_boundaries' => [$highTariffStart, $highTariffEnd],
    'consumption' => $consumption,
    'hourly_data' => $hourlyData,
    'rates' => ['low_rate' => $lowRate, 'high_rate' => $highRate, 'single_rate' => $singleRate]
], JSON_NUMERIC_CHECK);
