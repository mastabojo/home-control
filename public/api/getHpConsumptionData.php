<?php

$baseDir = dirname(__DIR__, 2);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

$highTariffStart = isset($ELECTRIC_POWER_HIGH_TARIFF_START) ? $ELECTRIC_POWER_HIGH_TARIFF_START : 6;
$highTariffEnd = isset($ELECTRIC_POWER_HIGH_TARIFF_END) ? $ELECTRIC_POWER_HIGH_TARIFF_END : 22;
$lowRate = isset($ELECTRIC_POWER_LOW_RATE) ? $ELECTRIC_POWER_LOW_RATE : 0.04391;
$highRate = isset($ELECTRIC_POWER_HIGH_RATE) ? $ELECTRIC_POWER_HIGH_RATE : 0.07918;
$singleRate = isset($ELECTRIC_POWER_SINGLE_RATE) ? $ELECTRIC_POWER_SINGLE_RATE : 0.07069;

// Get all tariff readings for current day from database
$q = "SELECT HOUR(read_time) AS read_hour, ROUND(MAX(total_energy), 2) AS read_energy 
FROM heat_pump_KWh WHERE read_time LIKE CONCAT(CURDATE(), '%') GROUP BY read_hour;";
$stmt = $DB->prepare($q);
$stmt->execute();
$rows_all_tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Min and max values for tariffs
$lowTariffMin1 = isset($rows_all_tariffs[0]['read_energy']) ? $rows_all_tariffs[0]['read_energy'] : 0;
$lowTariffMax1 = 0;
$highTariffMin = 0;
$highTariffMax = 0;
$lowTariffMin2 = 0;
$lowTariffMax2 = 0;

// Rearrange hourly readings into indexed array
$hourlyData = [];

// To ensure correct sequence of indexes
$controlIndex = 0;

foreach($rows_all_tariffs as $key => $hourly) {
    // rearrange data
    $index = $hourly['read_hour'];
    if($index == $controlIndex) {
        $hourlyData[$controlIndex] =  $hourly['read_energy'];
    } else {
        $hourlyData[$controlIndex] = isset($rows_all_tariffs[$key - 1]) ? $rows_all_tariffs[$key - 1] : 0;
    }

    // Get min and max readings for tarrifs (min tariff is before and after high tariff) 
    if($index < $highTariffStart) {
        $lowTariffMax1 = $hourly['read_energy'];
    } elseif($index == $highTariffStart) {
        $highTariffMin = $lowTariffMax1;
        $highTariffMax = $hourly['read_energy'];
    } elseif($index > $highTariffStart && $index < $highTariffEnd) {
        $highTariffMax = $hourly['read_energy'];
    } elseif($index == $highTariffEnd) {
        $highTariffMax = $hourly['read_energy'];
        $lowTariffMin2 = $hourly['read_energy'];
        $lowTariffMax2 = $hourly['read_energy'];
    } else {
        $lowTariffMax2 = $hourly['read_energy'];
    }
    $controlIndex++;
}

// Get totals
$totalMin = isset($rows_all_tariffs[0]['read_energy']) ? $rows_all_tariffs[0]['read_energy'] : 0;
$totalMax = end($hourlyData);

// Prepare consumption info
$consumption = [
    'lowTariff' => round((($lowTariffMax1 - $lowTariffMin1) + ($lowTariffMax2 - $lowTariffMin2)), 2),
    'highTariff' => round(($highTariffMax - $highTariffMin), 2),
    'total' => round(($totalMax - $totalMin), 2)
];

// return JSON encoded data
echo json_encode([
    'consumption' => $consumption,
    'hourlyData' => $hourlyData,
    'rates' => ['low_rate' => $lowRate, 'high_rate' => $highRate, 'single_rate' => $singleRate]
], JSON_NUMERIC_CHECK);
