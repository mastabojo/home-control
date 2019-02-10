<?php
/**
 * CPU temeprature logger
 * Run it with cron if CPU temeprature needs to be logged 
 */

include_once dirname(__DIR__) . '/env.php';
include_once dirname(__DIR__) . '/lib//functions.php';

$cpuTemperatureInfoPath = '/sys/class/thermal/thermal_zone0/temp';

// Read CPU temeperature form the system file
$cpuTemperature = round(intval(file($cpuTemperatureInfoPath)[0])/1000, 0);

// Chuck it into the database
try {
    $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
    $stmt = $DB->prepare("INSERT INTO cpu_temperature_log (`read_time`, `cpu_temperature`) VALUES (:read_time, :cpu_temperature);");
    $stmt->execute([':read_time' => time(), ':cpu_temperature' => $cpuTemperature]);
} catch(Exception $e) {
    logError('ERROR storing CPU temperature into the database', $e->getMessage());
}
