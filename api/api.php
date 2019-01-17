<?php
// THIS IS JUST A TEST, WILL MAKE A REAL API AT SOME STAGE
include_once dirname(__DIR__) . '/api/class.Weather.php';
$Weather = new Weather;
$w = $Weather->getWeatherCurrentDigest();
print_r($w);
