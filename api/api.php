<?php
// THIS IS JUST A TEST, WILL MAKE A REAL API AT SOME STAGE
include_once dirname(__DIR__) . '/api/class.Weather.php';
$Weather = new Weather;
// $w_current = $Weather->getWeatherCurrentDigest();
$w_forecast = $Weather->getWeatherForecastDigest();

var_export($w_forecast);
echo "\n\n";