<?php
include_once dirname(__DIR__) . '/env.php';

$cpuTemperatureInfoPath = '/sys/class/thermal/thermal_zone0/temp';
$cpuTemperature = file($cpuTemperatureInfoPath);

echo "TEMPERATURE: $cpuTemperature\n";

