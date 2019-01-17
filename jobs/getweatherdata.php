<?php
/*
  This script is meant to be run periodicaly (every 10 minutes or so) by a cron job
  The openweathermap.org limit of 60 requests per minute per one API key shall not be exceeded
*/

include_once dirname(__DIR__) . '/env.php';
include_once dirname(__DIR__) . '/functions.php';

$lang = isset($LANGUAGE) ? $LANGUAGE : 'en';

// City ID
$owmCityId = isset($OPENWEATHERMAP_CITY_ID) ? $OPENWEATHERMAP_CITY_ID : 3196359; // Defaults to Ljubljana

// API call for current weather
// https://openweathermap.org/current
$owmUrlWeather = 'http://api.openweathermap.org/data/2.5/weather?id=' . $owmCityId . '&units=metric&lang=' . $lang . '&APPID=' . $OPENWEATHERMAP_API_KEY;

// Get weather data
$owmDataJSON = file_get_contents($owmUrlWeather);
$owmData = json_decode($owmDataJSON, true);

$data['calc_time']           = isset($owmData['dt']) ? $owmData['dt'] : -1;
$data['city_id']             = isset($owmData['id']) ? $owmData['id'] : -1;
$data['weather_id']          = isset($owmData['weather'][0]['id']) ? $owmData['weather'][0]['id'] : -1;
$data['weather_main']        = isset($owmData['weather'][0]['main']) ? $owmData['weather'][0]['main'] : '';
$data['weather_description'] = isset($owmData['weather'][0]['description']) ? $owmData['weather'][0]['description'] : '';
$data['weather_icon']        = isset($owmData['weather'][0]['icon']) ? $owmData['weather'][0]['icon'] : '';
$data['temperature']         = isset($owmData['main']['temp']) ? $owmData['main']['temp'] : -100.0;
$data['pressure']            = isset($owmData['main']['pressure']) ? $owmData['main']['pressure'] : -1;
$data['humidity']            = isset($owmData['main']['humidity']) ? $owmData['main']['humidity'] : -1;
$data['wind_speed']          = isset($owmData['wind']['speed']) ? $owmData['wind']['speed'] : -1;
$data['wind_direction']      = isset($owmData['wind']['deg']) ? $owmData['wind']['deg'] : -1;
$data['clouds_all']          = isset($owmData['clouds']['all']) ? $owmData['clouds']['all'] : -1;
$data['rain_1h']             = isset($owmData['rain']['1h']) ? $owmData['rain']['1h'] : -1;
$data['rain_3h']             = isset($owmData['rain']['3h']) ? $owmData['rain']['3h'] : -1;
$data['snow_1h']             = isset($owmData['snow']['1h']) ? $owmData['snow']['1h'] : -1;
$data['snow_3h']             = isset($owmData['snow']['3h']) ? $owmData['snow']['3h'] : -1;
$data['sunrise']             = isset($owmData['sys']['sunrise']) ? $owmData['sys']['sunrise'] : -1;
$data['sunset']              = isset($owmData['sys']['sunset']) ? $owmData['sys']['sunset'] : -1; 

// if it is an ajax request return JSON
if(isset($_GET['req']) && $_GET['req'] == 'true') {

    echo json_encode($data, JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION);
    exit();

// If request comming from cron (not an ajax request) store the weather data into database
} else {

    $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

    if(!isset($DB) || $DB == false) {
        error_log("No connection to the database");
        exit();
    }

    $q = 'INSERT INTO weather_current (
        calc_time, 
        city_id,
        weather_id,
        weather_main,
        weather_description,
        weather_icon,
        temperature,
        pressure,
        humidity,
        wind_speed,
        wind_direction,
        clouds_all,
        rain_1h,
        rain_3h,
        snow_1h,
        snow_3h,
        sunrise,
        sunset
    ) VALUES (
        :calc_time, 
        :city_id,
        :weather_id,
        :weather_main,
        :weather_description,
        :weather_icon,
        :temperature,
        :pressure,
        :humidity,
        :wind_speed,
        :wind_direction,
        :clouds_all,
        :rain_1h,
        :rain_3h,
        :snow_1h,
        :snow_3h,
        :sunrise,
        :sunset
    );';            

    // Prepare and execute SQL insert statement
    $stmt = $DB->prepare($q);
    try {
        $stmt->execute($data);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
        error_log('Could not get weather current data: ' . $e->getMessage());
    }
}