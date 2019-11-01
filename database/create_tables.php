<?php
include '../env.php';
include '../lib/functions.php';

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

// drop tables if exist before creating (data will be lost)
$dropTablesIfExists = true;

// uncomment tables to be created
// make sure not to drop tables containing real data
$createTables = [
    // 'weather_current',
    // 'weather_forecast',
    // 'cities',
    // 'heat_pump_readings',
    // 'heat_pump_KWh',
    // 'hccusers',
    // 'holiday_dates',
    // 'cpu_temperature_log',
    // 'temp_and_humidity_readings',
    // 'app_settings',
    // 'system_data',
];

// Create table current_weather
$table = 'weather_current';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
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
$table = 'weather_forecast';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
    `forecast_id` int(11) NOT NULL AUTO_INCREMENT,
    `forecast_json` text CHARACTER SET utf8 NOT NULL,
    PRIMARY KEY (`forecast_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// Create cities table (city data from openweathermap.org)
$table = 'cities';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
    `city_id` int(11) NOT NULL,
    `city_name` varchar(64) CHARACTER SET utf8 NOT NULL,
    `city_country` varchar(8) CHARACTER SET utf8 NOT NULL,
    PRIMARY KEY (`city_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='City data from openweathermap.org';";

// Create heat_pump_readings table
$table = 'heat_pump_readings';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `read_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `phase_1_to_neutral` float NOT NULL DEFAULT '0',
    `phase_2_to_neutral` float NOT NULL DEFAULT '0',
    `phase_3_to_neutral` float NOT NULL DEFAULT '0',
    `phase_1_current` float NOT NULL DEFAULT '0',
    `phase_2_current` float NOT NULL DEFAULT '0',
    `phase_3_current` float NOT NULL DEFAULT '0',
    `phase_1_angle` float NOT NULL DEFAULT '0',
    `phase_2_angle` float NOT NULL DEFAULT '0',
    `phase_3_angle` float NOT NULL DEFAULT '0',
    `average_to_neutral` float NOT NULL DEFAULT '0',
    `average_current` float NOT NULL DEFAULT '0',
    `sum_current` float NOT NULL DEFAULT '0',
    `total_phase_angle` float NOT NULL DEFAULT '0',
    `input_frequency` float NOT NULL DEFAULT '0',
    `total_energy` float NOT NULL DEFAULT '0',
    `tariff` enum('vt','mt','et','') NOT NULL DEFAULT 'vt',
    PRIMARY KEY (`id`)
   ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

// Create heat pump KWh table
$table = 'heat_pump_KWh';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `read_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `total_energy` float NOT NULL DEFAULT '0',
    `tariff` enum('vt','mt','et','') NOT NULL DEFAULT 'vt',
    PRIMARY KEY (`id`)
   ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

// Create hccusers table
$table = 'hccusers';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
    `userid` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(40) NOT NULL,
    `passwrd` varchar(80) NOT NULL,
    `userlevel` smallint(6) NOT NULL,
    `firstname` varchar(40) NOT NULL,
    `lastname` varchar(40) NOT NULL,
    PRIMARY KEY (`userid`)
   ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;";
   
// Create holidays table
$table = 'holiday_dates';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `country_code` char(3) NOT NULL,
    `holiday_date` char(5) NOT NULL,
    `holiday_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
    `non_working_day` enum('y','n') NOT NULL DEFAULT 'y',
    PRIMARY KEY (`id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// Create CPU temperature log table
$table = 'cpu_temperature_log';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
    `read_time` int(11) NOT NULL,
    `cpu_temperature` int(11) NOT NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// Create temp_and_humidity_readings
$table = 'temp_and_humidity_readings';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `read_time` datetime NOT NULL,
    `sensor_id` varchar(5) CHARACTER SET utf8 NOT NULL,
    `temperature` float NOT NULL,
    `humidity` float NOT NULL,
    PRIMARY KEY (`id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=latin1";

// Create app settings table
$table = 'app_settings';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
    `name` varchar(64) NOT NULL,
    `value` varchar(512) NOT NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// Create system table
$table = 'system_data';
$qry[$table]  = $dropTablesIfExists ? "DROP TABLE IF EXISTS $table;\n" : '';
$qry[$table] .= "CREATE TABLE `$table` (
    `name` varchar(64) NOT NULL,
    `value` varchar(512) NOT NULL,
    'updated_at' datetime DEFAULT NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// Create selected tables
foreach($qry as $key => $q) {
    // create only selected tables present in the $createTables array
    if(in_array($key, $createTables)) {
        echo $q . "\n\n";
        $stmt = $DB->prepare($q);
        $stmt->execute();
    }
}

// Create event for deleting CPU temperature log entries older than 30 days
$qry = "
CREATE EVENT clear_cpu_temp_log
ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1 DAY
ON COMPLETION PRESERVE
DO
DELETE FROM cpu_temperature_log WHERE read_time < (UNIX_TIMESTAMP() - (60 * 60 * 24 * 30))";