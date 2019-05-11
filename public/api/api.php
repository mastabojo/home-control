<?php
// THIS IS JUST A TEST, WILL MAKE A REAL API AT SOME STAGE

/**/
// Test holidays
// include_once dirname(__DIR__) . '/api/class.CalendarHolidays.php';
// $c = new CalendarHolidays();
// print_r($c->getHolidayDates());

/*
// Test garbage collection stuff
include_once dirname(__DIR__) . '/api/class.GarbageCollection.php';
$g = new GarbageCollection();
print_r($g->getAllDates());
// print_r($g->getDatesForType('bio'));
*/

/**/
// Test weather class
// ------------------
// include_once dirname(__DIR__) . '/api/class.WeatherOWM.php';
include_once dirname(__DIR__) . '/api/class.WeatherARSO.php';
// $Weather = new WeatherOWM;
$Weather = new WeatherARSO;
// $weather = $Weather->getWeatherCurrentDigest();
$weather = $Weather->getWeatherForecastDigest();
echo "\n\n";
print_r($weather);
echo "\n\n";
