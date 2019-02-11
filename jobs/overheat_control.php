<?php
/**
 * Script to shutdown computer is CPU temeperature is above critical
 * Run this script from crontab (root)
 */

$cpuTemperatureInfoPath = '/sys/class/thermal/thermal_zone0/temp';

$criticalTemp = 82000;

// Read CPU temeperature form the system file
$cpuTemperature = intval(file($cpuTemperatureInfoPath)[0]);
// echo $cpuTemperature . "\n";
if($cpuTemperature > $criticalTemp) {
    logError("CPU temperature reached {$cpuTemperature} degrees, shutting down!");
    system('shutdown now');
}
