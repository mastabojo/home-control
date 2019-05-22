<?php

$baseDir = dirname(__DIR__, 2);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

$lang = isset($LANGUAGE) ? $LANGUAGE : 'en';

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

$today = date("Y-m-d");
$lowTariff = 'mt';
$highTariff = 'vt';

// Get min and max reading and difference for current day
$q = "SELECT tariff, ROUND(MAX(total_energy) - MIN(total_energy), 2) AS total_consumption 
FROM heat_pump_KWh WHERE read_time LIKE CONCAT(CURDATE(), '%') GROUP BY tariff;";
$stmt = $DB->prepare($q);
$stmt->execute();
$daily = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalDaily = 0;
$hiTariffDaily = 0;

// low tariff is actually total consumption for a day
// so data has to be rearranged
foreach($daily as $d) {
    if($d['tariff'] == 'mt') {
        $totalDaily = $d['total_consumption'];
    }
    if($d['tariff'] == 'vt') {
        $hiTariffDaily = $d['total_consumption'];
    }
}
$consumption = ['vt' => $hiTariffDaily, 'mt' => round($totalDaily - $hiTariffDaily, 2)];


// Get all tariff readings for current day from database
$q = "SELECT HOUR(read_time) AS read_hour, ROUND(MAX(total_energy), 2) AS read_energy, tariff 
FROM heat_pump_KWh WHERE read_time LIKE CONCAT(CURDATE(), '%') GROUP BY read_hour, tariff;";
$stmt = $DB->prepare($q);
$stmt->execute();
$rows_all_tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Rearrange hourly readings into indexed array
$hpData = [];

// To ensure correct sequence of indexes
$controlIndex = 0;

foreach($rows_all_tariffs as $key => $hourly) {
    $index = $hourly['read_hour'];
    if($index == $controlIndex) {
        $hpData[$index] = ['read_energy' => $hourly['read_energy'], 'tariff' => $hourly['tariff']];
    } else {
        $hpData[$index] = isset($rows_all_tariffs[$key - 1]) ? $rows_all_tariffs[$key - 1] : 0;
    }
    $controlIndex++;
}

$first = $hpData[0]['read_energy'];
$last = end($hpData)['read_energy'];

// return JSON encoded data
echo json_encode([
    'consumption' => $consumption,
    'chartData' => $hpData,
], JSON_NUMERIC_CHECK);
