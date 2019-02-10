<?php
include '../env.php';

$cpuTemperatureInfoPath = '/sys/class/thermal/thermal_zone0/temp';
$cpuTemperature = file($cpuTemperatureInfoPath);

echo "TEMPERATURE: $temperature";

