<?php

/**/
// Generate insert statements for fake heat pump readings

$valueKwh = 3890.9;
$tsStart = '1546297200'; // 1.1.2019 0:00
$interval = 10 * 60; // Interval in seconds
$tsStop = time(); // Now

echo 'INSERT INTO heat_pump_readings (heat_pump_id, read_time, read_kwh) VALUES ';
for($ts = $tsStart; $ts < $tsStop; $ts += $interval) {
    $valueKwh = round($valueKwh + rand(20, 60) / 100, 2);
    echo '<pre>';
    echo "(1, $ts, $valueKwh),";
    echo '</pre>';
}
