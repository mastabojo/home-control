<?php

include_once dirname(__DIR__) . '/env.php';
include_once dirname(__DIR__) . '/lib/functions.php';

$lang = isset($LANGUAGE) ? $LANGUAGE : 'en';

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);



$dt = new DateTime();




$timezoneOffset = (60 * 60);
$daySeconds = 60 * 60 * 24;
$offset = $daySeconds * 5;
$startOfToday = strtotime(date("d-m-Y", time() + $timezoneOffset));

$start = $startOfToday - $offset;
$end = $startOfToday + (24 * 60 * 60) - $offset;

// echo 'Start: ' . $start . ' [' . date("d.m.Y h:s", $start) . "]\n";
// echo 'End: ' . $end . ' [' . date("d.m.Y h:s", $end) . "]\n";

$q = "SELECT read_time, read_kwh, read_time_2 AS realdate FROM heat_pump_readings 
WHERE read_time_2 >= FROM_UNIXTIME($start) AND read_time <= FROM_UNIXTIME($end)";

// echo "$q\n";