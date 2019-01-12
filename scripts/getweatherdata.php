<?php
include_once '/env.php';
/*
  OpenWeatherMap City IDs: 
    Sostro - 3190310
    Ljubljana - 3196359
    Zalog - 3186832, 
*/
// City ID and name
$owmCityData = ['id' => 3190310, 'name' => 'Sostro'];

// API call for current weather
// https://openweathermap.org/current
$owmUrlWeather = 'http://api.openweathermap.org/data/2.5/weather?id=' . $owmCityData['id'] . '&units=metric&lang=sl&APPID=' . $OPENWEATHERMAP_API_KEY;

// API call for 5 days / 3 houly forecast
// https://openweathermap.org/forecast5
$owmUrlForecast = 'http://api.openweathermap.org/data/2.5/forecast?id=' . $owmCityData['id'] . '&units=metric&lang=sl&APPID=' . $OPENWEATHERMAP_API_KEY;

// current API URL
$owmUrl = $owmUrlForecast;

// Weather data
$owmData = json_decode(file_get_contents($owmUrl), true);