<?php
// THIS IS JUST A TEST, WILL MAKE A REAL API AT SOME STAGE
include_once dirname(__DIR__) . '/api/class.WeatherOWM.php';
$Weather = new WeatherOWM;
// $weather = $Weather->getWeatherCurrentDigest();
$weather = $Weather->getWeatherForecastDigest();

var_export($weather);
// echo "\n\n";