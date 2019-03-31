<?php
/*
  This script is meant to be run periodicaly (every 10 minutes or so) by a cron job
  Possible request limit shall not be exceeded (i.e. openweathermap.org limit is 60 requests per minute per one API key)
*/

include_once dirname(__DIR__) . '/env.php';
include_once dirname(__DIR__) . '/lib/functions.php';
include_once dirname(__DIR__) . '/public/api/class.WeatherARSO.php';

$lang = isset($LANGUAGE) ? $LANGUAGE : 'en';

$w = new WeatherARSO();

// if it is an ajax request return JSON
if(isset($_GET['req']) && $_GET['req'] == 'true') {

    echo $w->getWeatherCurrentDigestJSON();
    exit();

// If request comming from cron (not an ajax request) store the weather data into database
} else {

    $data = $w->getWeatherCurrentDigest();
    
    // city name des not go into database
    if(isset($data['city_name'])) {
        unset($data['city_name']);
    }

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