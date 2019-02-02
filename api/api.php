<?php
// THIS IS JUST A TEST, WILL MAKE A REAL API AT SOME STAGE
// include_once dirname(__DIR__) . '/api/class.WeatherOWM.php';
include_once dirname(__DIR__) . '/api/class.WeatherARSO.php';
// $Weather = new WeatherOWM;
$Weather = new WeatherARSO;
$weather = $Weather->getWeatherCurrentDigest();
// $weather = $Weather->getWeatherForecastDigest();
echo "\n\n";
print_r($weather);
echo "\n\n";