<?php
/*
  This script is meant to be run periodicaly (every 10 minutes or so) by a cron job
  The openweathermap.org limit of 60 requests per minute per one API key shall not be exceeded
*/
include_once '../env.php';
include_once '../functions.php';

$lang = isset($LANGUAGE) ? $LANGUAGE : 'en';

// City ID
$owmCityId = isset($OPENWEATHERMAP_CITY_ID) ? $OPENWEATHERMAP_CITY_ID : 3196359; // Defaults to Ljubljana

// API call for current weather
// https://openweathermap.org/current
$owmUrlWeather = 'http://api.openweathermap.org/data/2.5/weather?id=' . $owmCityId . '&units=metric&lang=' . $lang . '&APPID=' . $OPENWEATHERMAP_API_KEY;

// Get weather data
$owmData = json_decode(file_get_contents($owmUrlWeather), true);

// print_r($owmData);

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
    $stmt->execute([
        'calc_time'           => isset($owmData['dt']) ? $owmData['dt'] : -1,
        'city_id'             => isset($owmData['id']) ? $owmData['id'] : -1,
        'weather_id'          => isset($owmData['weather'][0]['id']) ? $owmData['weather'][0]['id'] : -1,
        'weather_main'        => isset($owmData['weather'][0]['main']) ? $owmData['weather'][0]['main'] : '',
        'weather_description' => isset($owmData['weather'][0]['description']) ? $owmData['weather'][0]['description'] : '',
        'weather_icon'        => isset($owmData['weather'][0]['icon']) ? $owmData['weather'][0]['icon'] : '',
        'temperature'         => isset($owmData['main']['temp']) ? $owmData['main']['temp'] : -100.0,
        'pressure'            => isset($owmData['main']['pressure']) ? $owmData['main']['pressure'] : -1,
        'humidity'            => isset($owmData['main']['humidity']) ? $owmData['main']['humidity'] : -1,
        'wind_speed'          => isset($owmData['wind']['speed']) ? $owmData['wind']['speed'] : -1,
        'wind_direction'      => isset($owmData['wind']['deg']) ? $owmData['wind']['deg'] : -1,
        'clouds_all'          => isset($owmData['clouds']['all']) ? $owmData['clouds']['all'] : -1,
        'rain_1h'             => isset($owmData['rain']['1h']) ? $owmData['rain']['1h'] : -1,
        'rain_3h'             => isset($owmData['rain']['3h']) ? $owmData['rain']['3h'] : -1,
        'snow_1h'             => isset($owmData['snow']['1h']) ? $owmData['snow']['1h'] : -1,
        'snow_3h'             => isset($owmData['snow']['3h']) ? $owmData['snow']['3h'] : -1,
        'sunrise'             => isset($owmData['sys']['sunrise']) ? $owmData['sys']['sunrise'] : -1,
        'sunset'             => isset($owmData['sys']['sunset']) ? $owmData['sys']['sunset'] : -1 
    ]);
} catch (PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
    error_log('Could not get weather current data: ' . $e->getMessage());
    echo('Could not get weather current data: ' . $e->getMessage());
}
