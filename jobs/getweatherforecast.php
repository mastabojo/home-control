<?php
/*
  This script is meant to be run periodicaly (every 3 hours or so) by a cron job
  The openweathermap.org limit of 60 requests per minute per one API key shall not be exceeded
*/

include_once dirname(__DIR__) . '/env.php';
include_once dirname(__DIR__) . '/functions.php';

$lang = isset($LANGUAGE) ? $LANGUAGE : 'en';

// City ID
$owmCityId = isset($OPENWEATHERMAP_CITY_ID) ? $OPENWEATHERMAP_CITY_ID : 3196359; // Defaults to Ljubljana

// API call for 5 days / 3 houly forecast
// https://openweathermap.org/forecast5
$owmUrlForecast = 'http://api.openweathermap.org/data/2.5/forecast?id=' . $owmCityId . '&units=metric&lang=' . $lang . '&APPID=' . $OPENWEATHERMAP_API_KEY;

// Get forecast for 5 days
$owmDataJSON = file_get_contents($owmUrlForecast);

// if it is an ajax request return JSON
if(isset($_GET['req']) && $_GET['req'] == 'true') {

    echo $owmDataJSON;
    exit();

// If it is not an ajax request store the weather data into database
} else {

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
        throw new PDOException($e->getMessage(), (int)$e->getCode());
        error_log('Could not get weather forecast data: ' . $e->getMessage());
    }
}