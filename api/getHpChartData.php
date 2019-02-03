<?php

include_once dirname(__DIR__) . '/env.php';
include_once dirname(__DIR__) . '/lib/functions.php';

$lang = isset($LANGUAGE) ? $LANGUAGE : 'en';

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

// get start and end timestamp for a given day (default today)
$tsArr = getDayStartAndEndTs();

// Get all readings for current day from database
$q = "SELECT read_time, read_kwh FROM heat_pump_readings WHERE read_time >= :startTs AND read_time <= :endTs";
// echo "$q\n";
$stmt = $DB->prepare($q);
$stmt->execute([':startTs' => $tsArr[0], ':endTs' => $tsArr[1]]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentHour = 0;
$currentReadings = [];
$hourlyReadings = [];
$hourlyAverages = [];
$dailyFirst = 0;
$dailyLast = 0;
$lastKey = count($rows)  - 1;
foreach($rows as $key => $row) {

    // Save first and last daily reading
    if($key == 0) {
        $dailyFirst = $row['read_kwh'];
    } else if($key == $lastKey) {
        $dailyLast = $row['read_kwh'];
    }

    $dt = new DateTime();
    $readHour = $dt->setTimestamp($row['read_time'])->format('G');
    $currentReadings[] = $row['read_kwh'];

    if($readHour != $currentHour) {
        $readingsCount = count($currentReadings);
        if($readingsCount > 0) {
            $hourlyReadings[$currentHour] = $currentReadings;
        } else {
            $hourlyReadings[$currentHour] = [];
        }
        $currentReadings = [];
        $currentHour++;
    }
}

// get averages for each hour
// code better method for averages, this one is not appropriate
foreach($hourlyReadings as $hour => $readings) {
    $hourlyAverages[$hour] = (max($readings) - min($readings)) / count($readings);
}

// get total daily readings
$DailyTotalReadings = $dailyLast - $dailyFirst;
$dailyAverage =  $DailyTotalReadings / count($rows);

print_r($hourlyAverages);
print_r('Total daily readings: ' . $DailyTotalReadings . "($dailyLast - $dailyFirst)" . ', daily average: ' . $dailyAverage . "\n\n");