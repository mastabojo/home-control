<?php

/*
// Generate insert statements for fake heat pump readings

$tsStart = '1546297200'; // 1.1.2019 0:00
$interval = 10 * 60; // Interval in seconds
$tsStop = time(); // Now

echo 'INSERT INTO heat_pump_readings (heat_pump_id, read_time, read_kwh) VALUES ';
for($ts = $tsStart; $ts < $tsStop; $ts += $interval) {
    $readValue = rand(20, 40) / 100;
    echo '<pre>';
    echo "(1, $ts, $readValue),";
    echo '</pre>';
}
*/