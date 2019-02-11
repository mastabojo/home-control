<?php
/**
 * CPU temeprature 
 * Read surrent CPU from the system and mx/min/avg from the database
 */

$baseDir = dirname(__DIR__, 2);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

$cpuTemperatureInfoPath = '/sys/class/thermal/thermal_zone0/temp';

// Read CPU temeperature form the system file
$cpuTemperature = round(intval(file($cpuTemperatureInfoPath)[0])/1000, 0);

$dt = new DateTime();
$startOfToday = $dt->setTime(0,0)->getTimestamp();
$endOfToday = $startOfToday + (24 * 60 * 60);

// Get min and max CPU temperature from the database
try {
    $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
    $q = "SELECT MIN(cpu_temperature) AS min_cpu_temperature, MAX(cpu_temperature) AS max_cpu_temperature FROM cpu_temperature_log 
    WHERE read_time > $startOfToday AND read_time < $endOfToday";
    $stmt = $DB->prepare($q);
    
    // echo "$q\n";
    
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if(isset($row) && !empty($row)) {
        echo json_encode(array_merge(['cpu_temperature' => $cpuTemperature], $row), JSON_FORCE_OBJECT);
    } else {
        "{'cpu_temperature': $cpuTemperature, 'min_cpu_temperature': -200, 'max_cpu_temperature': -100}";
    }
    exit();

} catch(Exception $e) {
    logError('ERROR reading min and max CPU temperature from the database', $e->getMessage());
    echo '';
}

