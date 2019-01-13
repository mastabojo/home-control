<?php
/*
  This script is meant to be run periodicaly (every hour or so) by a cron job
  The openweathermap.org limit of 60 requests per minute per one API key shall not be exceeded
*/

include_once '../env.php';
include_once '../functions.php';

$lang = isset($LANGUAGE) ? $LANGUAGE : 'en';

// City ID
$owmCityId = isset($OPENWEATHERMAP_CITY_ID) ? $OPENWEATHERMAP_CITY_ID : 3196359; // Defaults to Ljubljana

// API call for 5 days / 3 houly forecast
// https://openweathermap.org/forecast5
$owmUrlForecast = 'http://api.openweathermap.org/data/2.5/forecast?id=' . $owmCityId . '&units=metric&lang=' . $lang . '&APPID=' . $OPENWEATHERMAP_API_KEY;

// Get forecast for 5 days
$owmDataJSON = file_get_contents($owmUrlForecast);

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

if(!isset($DB) || $DB == false) {
    error_log("No connection to the database");
    exit();
}

$q = "INSERT INTO weather_forecast (forecast_json) VALUES (:forecast_json);";

// Prepare and execute SQL insert statement
$stmt = $DB->prepare($q);
try {
    $stmt->execute([
        'forecast_json' => isset($owmDataJSON) ? $owmDataJSON : '',
    ]);
} catch (PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
    error_log('Could not get weather forecast data: ' . $e->getMessage());
    echo('Could not get weather forecast data: ' . $e->getMessage());
}
