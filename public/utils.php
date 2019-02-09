<?php
error_reporting(E_ALL);
// Generate insert statements for fake heat pump readings

include '../lib/functions.php';

$valueKwh = 3890.9;
$tsStart = strtotime("1 January 2019");
$interval = 20 * 60; // Interval in seconds
$tsStop = time(); // Now

// holiday dates for power tariff
$holidays = [
    '01-01', // Novo leto
    '01-02', // Novo leto
    '02-08', // Prešernov dan 
    '04-07', // Dan upora proti okupatorju
    '05-01', // Praznik dela
    '05-02', // Praznik dela
    '06-25', // Dan državnosti
    '08-15', // Dan MV
    '10-31', // Danreformacije
    '11-01', // Dan spomina na mrtve
    '12-25', // Božič
    '12-26'  // Dan samostojnosti in enotnosti
];

echo '<pre>' . 'TRUNCATE TABLE heat_pump_readings;' . '</pre>';
echo '<pre>' . 'ALTER TABLE heat_pump_readings AUTO_INCREMENT=1;' . '</pre>';
echo '<pre>' . 'INSERT INTO heat_pump_readings (heat_pump_id, read_time, read_kwh, tariff) VALUES ' . '</pre>';
for($ts = $tsStart; $ts < $tsStop; $ts += $interval) {
    
    // create random read_value
    $valueKwh = round($valueKwh + rand(20, 60) / 100, 2);

    // get Easter date fo the year reading way made
    $easterDT = getEasterDatetime(date("Y", $ts));

    // check whether tariff is low 
    if(
        in_array(date("m-d", $ts), $holidays) ||            // if the day of reading is a holiday
        in_array(date("N", $ts), [6, 7]) ||                 // if the day of reading is Saturday or Sunday
        (date("G", $ts) >= 0 && date("G", $ts) < 6) ||      // if the day of reading is morning between 0 and 6
        (date("G", $ts) >= 22 && date("G", $ts) < 24) ||   // if the day of reading is afternoon between 10 and 24
        date("m-d-Y", $ts) == $easterDT->format("m-d-Y")    // if the day of reading is Easter (a moving target :-)
    ) {
        $tariff = 'mt';

    // or high
    } else {
        $tariff = 'vt';
    }
    
    echo '<pre>';
    echo "(1, $ts, $valueKwh, '$tariff'),";
    // echo ' -- ' . date('D, d.m.Y H:i', $ts);
    echo '</pre>';
}
