<?php
include_once dirname(__DIR__, 2) . '/lib/functions.php';

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

/*
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
*/

echo (isEasterMonday('2020-04-13')) ? "Y\n" : "N\n";
echo (isEasterMonday('2020-04-12')) ? "Y\n" : "N\n";
echo (isEasterMonday('2019-04-22')) ? "Y\n" : "N\n";
echo (isEasterMonday('2020-04-13')) ? "Y\n" : "N\n";
echo (isEasterMonday('2021-04-05')) ? "Y\n" : "N\n";
echo (isEasterMonday('2022-04-18')) ? "Y\n" : "N\n";
echo (isEasterMonday('2023-04-10')) ? "Y\n" : "N\n";
echo (isEasterMonday('2024-04-01')) ? "Y\n" : "N\n";
echo (isEasterMonday('2025-04-21')) ? "Y\n" : "N\n";
echo (isEasterMonday('2025-04-24')) ? "Y\n" : "N\n";
