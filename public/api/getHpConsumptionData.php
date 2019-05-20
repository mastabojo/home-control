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
$q = "SELECT ROUND(MAX(total_energy) - MIN(total_energy), 2) AS total_consumption 
FROM heat_pump_KWh WHERE read_time LIKE '$today%';";
$stmt = $DB->prepare($q);
$stmt->execute();
$daily = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
// Get all low tariff readings for current day from database
$q = "SELECT HOUR(read_time) AS read_hour, MAX(total_energy) 
FROM heat_pump_KWh WHERE read_time LIKE '$today%' AND tariff = '$lowTariff' group by read_hour;";
$stmt = $DB->prepare($q);
$stmt->execute();
$rows_low_tariff = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all high tariff readings for current day from database
$q = "SELECT HOUR(read_time) AS read_hour, MAX(total_energy) AS read_energy
FROM heat_pump_KWh WHERE read_time LIKE '$today%' AND tariff = '$highTariff' group by read_hour;";
$stmt = $DB->prepare($q);
$stmt->execute();
$rows_high_tariff = $stmt->fetchAll(PDO::FETCH_ASSOC);
*/

// Get all tariff readings for current day from database
$q = "SELECT HOUR(read_time) AS read_hour, ROUND(MAX(total_energy), 2) AS read_energy, tariff 
FROM heat_pump_KWh WHERE read_time LIKE '$today%' group by read_hour, tariff;";
$stmt = $DB->prepare($q);
$stmt->execute();
$rows_all_tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Rearrange hourly readings
$hpData = [];
foreach($rows_all_tariffs as $hourly) {
    $hpData[$hourly['read_hour']] = ['read_energy' => $hourly['read_energy'], 'tariff' => $hourly['tariff']];
}

// return JSON encoded data
echo json_encode([
    'consumption' => $daily[0]['total_consumption'],
    // 'rowsLowTariff' => $rows_low_tariff,
    // 'rowsHighTariff' => $rows_high_tariff,
    // 'rowsAllTariffs' => $rows_all_tariffs,
    'data' => $hpData,
], JSON_FORCE_OBJECT);
