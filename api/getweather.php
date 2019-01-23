<?php
/*
  Returns current weather or weather forecast, depending on the GET parameter
*/
if(!isset($_GET['type']) || ($_GET['type'] != 'current' && $_GET['type'] != 'forecast')) {
    error_log('Error: wrong parameter in ajax call ' . $_GET['type']);
    return '';
    exit();
}

include_once dirname(__DIR__) . '/env.php';
include_once dirname(__DIR__) . '/functions.php';

$lang = isset($LANGUAGE) ? $LANGUAGE : 'en';

// City ID
$cityId = isset($WEATHER_PROVIDER_CITY_ID) ? $WEATHER_PROVIDER_CITY_ID : 3196359; // Defaults to Ljubljana

if(isset($WEATHER_PROVIDER)) {
    
    $className = "Weather{$WEATHER_PROVIDER}";
    include_once(dirname(__DIR__) . "/api/class.{$className}.php");

    if(class_exists($className)) {

        $weather = new $className;

    } else {

        error_log("Class $className does not exist.");
        echo '';
        exit();
    }

} else {
    error_log("Weather provider not set");
    echo '';
    exit();
}

switch($_GET['type']) {
    case 'current':
        echo $weather->getWeatherCurrentDigestJSON();
        break;
    case 'forecast':
        echo $weather->getWeatherForecastDigestJSON();
        break;
    default:
}

