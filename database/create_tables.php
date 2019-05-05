<?php
include '../env.php';
include '../lib/functions.php';

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

// drop tables if exist before creating (data will be lost)
$dropTablesIfExists = true;

// uncomment tables to be created
$createTables = [
    // 'weather_current',
    // 'weather_forecast',
    // 'cities',
    // 'heat_pump_readings',
    // 'hccusers'
];

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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// Create table for weather forecasts - it will store JSON reading in TEXT fields
$qry['weather_forecast']  = '';
$qry['weather_forecast'] .= $dropTablesIfExists ? "DROP TABLE IF EXISTS weather_forecast;\n" : '';
$qry['weather_forecast'] .= "CREATE TABLE `weather_forecast` (
    `forecast_id` int(11) NOT NULL AUTO_INCREMENT,
    `forecast_json` text CHARACTER SET utf8 NOT NULL,
    PRIMARY KEY (`forecast_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

// Create cities table (city data from openweathermap.org)
$qry['cities']  = '';
$qry['cities'] .= $dropTablesIfExists ? "DROP TABLE IF EXISTS cities;\n" : '';
$qry['cities'] .= "CREATE TABLE `cities` (
    `city_id` int(11) NOT NULL,
    `city_name` varchar(64) CHARACTER SET utf8 NOT NULL,
    `city_country` varchar(8) CHARACTER SET utf8 NOT NULL,
    PRIMARY KEY (`city_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='City data from openweathermap.org';";

// Create heat_pump_readings table
$qry['heat_pump_readings'] = '';
$qry['heat_pump_readings'] .= $dropTablesIfExists ? "DROP TABLE IF EXISTS heat_pump_readings;\n" : '';
$qry['heat_pump_readings'] = "CREATE TABLE `heat_pump_readings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `read_time` int(11) NOT NULL,
    `total_energy` float NOT NULL DEFAULT 0.0,
    `phase_1_to_neutral` float NOT NULL DEFAULT 0.0,
    `phase_2_to_neutral` float NOT NULL DEFAULT 0.0,
    `phase_3_to_neutral` float NOT NULL DEFAULT 0.0,
    `average_to_neutral` float NOT NULL DEFAULT 0.0,
    `phase_1_current` float NOT NULL DEFAULT 0.0,
    `phase_2_current` float NOT NULL DEFAULT 0.0,
    `phase_3_current` float NOT NULL DEFAULT 0.0,
    `average_current` float NOT NULL DEFAULT 0.0,
    `sum_current` float NOT NULL DEFAULT 0.0,
    `phase_1_angle` float NOT NULL DEFAULT 0.0,
    `phase_2_angle` float NOT NULL DEFAULT 0.0,
    `phase_3_angle` float NOT NULL DEFAULT 0.0,
    `total_phase_angle` float NOT NULL DEFAULT 0.0,
    `input_frequency` float NOT NULL DEFAULT 0.0,
    `tariff` enum('vt','mt','et','') NOT NULL DEFAULT 'vt',
    PRIMARY KEY (`id`)
   ) ENGINE=InnoDB AUTO_INCREMENT=4987 DEFAULT CHARSET=latin1;";

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
   ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;";
   
// Create holidays table
$qry['holiday_dates'] = '';
$qry['holiday_dates'] .= $dropTablesIfExists ? "DROP TABLE IF EXISTS holiday_dates;\n" : '';
$qry['holiday_dates'] = "CREATE TABLE `holiday_dates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `country_code` char(3) NOT NULL,
    `holiday_date` char(5) NOT NULL,
    `holiday_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
    `non_working_day` enum('y','n') NOT NULL DEFAULT 'y',
    PRIMARY KEY (`id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// Create CPU temperature log table
$qry['cpu_temperature_log'] = '';
$qry['cpu_temperature_log'] .= $dropTablesIfExists ? "DROP TABLE IF EXISTS cpu_temperature_log;\n" : '';
$qry['cpu_temperature_log'] = 
"CREATE TABLE `cpu_temperature_log` (
    `read_time` int(11) NOT NULL,
    `cpu_temperature` int(11) NOT NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

// Create selected tables
foreach($qry as $key => $q) {
    // create only selected tables present in the $createTables array
    if(in_array($key, $createTables)) {
        echo $q . "\n\n";
        $stmt = $DB->prepare($q);
        $stmt->execute();
    }
}