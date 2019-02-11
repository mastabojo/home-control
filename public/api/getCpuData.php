<?php
/**
 * Read current CPU load and temperature from the system and max/min CPU temperature from the database
 */

$baseDir = dirname(__DIR__, 2);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

$cpuLoadArr = sys_getloadavg();
$numberOfCores = trim(shell_exec("grep -P '^model name' /proc/cpuinfo|wc -l"));

if(isset($cpuLoadArr[0]) && $numberOfCores > 0) {
    $cpuLoad = round($cpuLoadArr[0] / $numberOfCores, 2);
} else {
    $cpuLoad = '-1';
}

$cpuTemperatureInfoPath = '/sys/class/thermal/thermal_zone0/temp';

// Read CPU temeperature form the system file
$cpuTemperature = round(intval(file($cpuTemperatureInfoPath)[0])/1000, 0);

$dt = new DateTime();
$startOfToday = $dt->setTime(0,0)->getTimestamp();
$endOfToday = $startOfToday + (24 * 60 * 60);

// Get min and max CPU temperature from the database
try {
    $minCpuTemperature = -200;
    $maxCpuTemperature = -100;

    $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
    $q = "SELECT MIN(cpu_temperature) AS min_cpu_temperature, MAX(cpu_temperature) AS max_cpu_temperature FROM cpu_temperature_log 
    WHERE read_time > $startOfToday AND read_time < $endOfToday";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if(isset($row['min_cpu_temperature']) && isset($row['max_cpu_temperature'])) {
        $minCpuTemperature = $row['min_cpu_temperature'];
        $maxCpuTemperature = $row['max_cpu_temperature'];
    }

} catch(Exception $e) {
    logError('ERROR reading min and max CPU temperature from the database', $e->getMessage());
}

echo json_encode([
    'cpu_temperature' => $cpuTemperature,
    'min_cpu_temperature' => $minCpuTemperature,
    'max_cpu_temperature' => $maxCpuTemperature,
    'cpu_load' => $cpuLoad
], JSON_FORCE_OBJECT);