<?php
$dropTablesIfExists = true;

// Create table current_weather
$qry['weather_current']  = '';
$qry['weather_current'] .= $dropTablesIfExists ? "DROP TABLE IF EXISTS weather_current;\n" : '';
$qry['weather_current'] .= "CREATE TABLE `weather_current` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `calc_time` int(11) NOT NULL,
    `city_id` int(11) NOT NULL,
    `weather_id` int(8) NOT NULL,
    `weather_main` varchar(128) NOT NULL,
    `weather_description` varchar(248) NOT NULL,
    `weather_icon` varchar(16) NOT NULL,
    `temperature` float NOT NULL,
    `pressure` int(11) NOT NULL,
    `humidity` int(11) NOT NULL,
    `wind_speed` float NOT NULL,
    `wind_direction` int(6) NOT NULL,
    `clouds_all` int(11) NOT NULL,
    `rain_1h` int(6) NOT NULL,
    `rain_3h` int(6) NOT NULL,
    `snow_1h` int(6) NOT NULL,
    `snow_3h` int(6) NOT NULL,
    `sunrise` int(11) NOT NULL,
    `sunset` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `curent_weather_time` (`calc_time`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

// Create table for weather forecasts - it will store JSON reading in TEXT fields
$qry['weather_forecast']  = '';
$qry['weather_forecast'] .= $dropTablesIfExists ? "DROP TABLE IF EXISTS weather_forecast;\n" : '';
$qry['weather_forecast'] .= "CREATE TABLE `weather_forecast` (
    `forecast_id` int(11) NOT NULL AUTO_INCREMENT,
    `forecast_json` text CHARACTER SET utf8 NOT NULL,
    PRIMARY KEY (`forecast_id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=latin1";

// Create cities table (city data from openweathermap.org)
$qry['cities']  = '';
$qry['cities'] .= $dropTablesIfExists ? "DROP TABLE IF EXISTS cities;\n" : '';
$qry['cities'] .= "CREATE TABLE `cities` (
    `city_id` int(11) NOT NULL,
    `city_name` varchar(64) CHARACTER SET utf8 NOT NULL,
    `city_country` varchar(8) CHARACTER SET utf8 NOT NULL,
    PRIMARY KEY (`city_id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='City data from openweathermap.org'";

foreach($qry as $q) {
    echo $q . "\n\n";
}

// Create hccusers table
$qry['hccusers'] = '';
$qry['hccusers'] .= $dropTablesIfExists ? "DROP TABLE IF EXISTS hccusers;\n" : '';
$qry['hccusers'] = "CREATE TABLE `hccusers` (
    `userid` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(40) NOT NULL,
    `passwrd` varchar(80) NOT NULL,
    `userlevel` smallint(6) NOT NULL,
    `firstname` varchar(40) NOT NULL,
    `lastname` varchar(40) NOT NULL,
    PRIMARY KEY (`userid`)
   ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1";