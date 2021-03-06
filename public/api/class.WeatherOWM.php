<?php
/*
  Weather current data and forecast API
  Data read form opeweathermap.org API
*/
include(dirname(__DIR__) . '/api/class.Weather.php');
class WeatherOWM extends Weather
{
    public function __construct() {
        
        parent::__construct();

        include $this->baseDir . '/env.php';

        $this->apiCurrentUrl  = "http://api.openweathermap.org/data/2.5/weather?id={$OPENWEATHERMAP_CITY_ID}&units=metric&lang={$LANGUAGE}&APPID={$OPENWEATHERMAP_API_KEY}";
        $this->apiForecastUrl = "http://api.openweathermap.org/data/2.5/forecast?id={$OPENWEATHERMAP_CITY_ID}&units=metric&lang={$LANGUAGE}&APPID={$OPENWEATHERMAP_API_KEY}";
    }

    /**
     * Get current weather basic data from openweathermap.org
     * @retturn array Basic weather data
     */
    public function getWeatherCurrentDigest() 
    {
        // Get weather data
        $owmData = json_decode(file_get_contents($this->apiCurrentUrl), true);

        $data = [];
        $data['calc_time']           = isset($owmData['dt']) ? $owmData['dt'] : -1;
        $data['city_id']             = isset($owmData['id']) ? $owmData['id'] : -1;
        $data['city_name']           = isset($owmData['name']) ? $owmData['name'] : '';
        $data['weather_id']          = isset($owmData['weather'][0]['id']) ? $owmData['weather'][0]['id'] : -1;
        $data['weather_main']        = isset($owmData['weather'][0]['main']) ? $owmData['weather'][0]['main'] : '';
        $data['weather_description'] = isset($owmData['weather'][0]['description']) ? $owmData['weather'][0]['description'] : '';
        $data['weather_icon']        = isset($owmData['weather'][0]['icon']) ? $owmData['weather'][0]['icon'] : '';
        $data['temperature']         = isset($owmData['main']['temp']) ? $owmData['main']['temp'] : -100.0;
        $data['pressure']            = isset($owmData['main']['pressure']) ? $owmData['main']['pressure'] : -1;
        $data['humidity']            = isset($owmData['main']['humidity']) ? $owmData['main']['humidity'] : -1;
        $data['wind_speed']          = isset($owmData['wind']['speed']) ? $owmData['wind']['speed'] : -1;
        $data['wind_direction']      = isset($owmData['wind']['deg']) ? $owmData['wind']['deg'] : -1;
        $data['clouds_all']          = isset($owmData['clouds']['all']) ? $owmData['clouds']['all'] : -1;
        $data['rain_1h']             = isset($owmData['rain']['1h']) ? $owmData['rain']['1h'] : -1;
        $data['rain_3h']             = isset($owmData['rain']['3h']) ? $owmData['rain']['3h'] : -1;
        $data['snow_1h']             = isset($owmData['snow']['1h']) ? $owmData['snow']['1h'] : -1;
        $data['snow_3h']             = isset($owmData['snow']['3h']) ? $owmData['snow']['3h'] : -1;
        $data['sunrise']             = isset($owmData['sys']['sunrise']) ? $owmData['sys']['sunrise'] : -1;
        $data['sunset']              = isset($owmData['sys']['sunset']) ? $owmData['sys']['sunset'] : -1;

        return $data;
    }

    /**
     * Get current weather basic data from openweathermap.org
     * @retturn string Basic weather data in JSON
     */
    public function getWeatherCurrentDigestJSON() 
    {
        return json_encode($this->getWeatherCurrentDigest(), JSON_FORCE_OBJECT);
    }

    /**
     * Get current weather basic data from openweathermap.org
     * @retturn array Basic weather data
     * THIS IS OLD VERSION. SEE THE REFACTORED VERSION BELOW
     */
    public function getWeatherForecastDigest_OLD() 
    {
        // Get weather forecast data
        $owmData = json_decode(file_get_contents($this->apiForecastUrl), true);

        $dataKey = 0;
        $data = [];
        
        foreach($owmData['list'] as $fcNo => $dailyForecast) {
            
            // day of the week for array index
            $dayNo = date('w', $dailyForecast['dt']);

            // Current hur for finding out day/night reading
            $currentHour = date('G', $dailyForecast['dt']);

            $data[$dataKey]['day_no'] = $dayNo;

            // read night temeprature only if hour falls between 0 and 2
            if($currentHour >= 0 && $currentHour < 3) {
                $data[$dataKey]['temperature_night'] = isset($dailyForecast['main']['temp']) ? $dailyForecast['main']['temp'] : -100.0;
                $data[$dataKey]['calc_time'] = isset($dailyForecast['dt']) ? $dailyForecast['dt'] : -1;
                $data[$dataKey]['weather_description'] = isset($dailyForecast['weather'][0]['description']) ? $dailyForecast['weather'][0]['description'] : '';
                $data[$dataKey]['weather_icon'] = isset($dailyForecast['weather'][0]['icon']) ? $dailyForecast['weather'][0]['icon'] : '';
            // read day temeprature only if hour falls between 12 and 14
            } elseif($currentHour >= 12 && $currentHour < 15) {
                $data[$dataKey]['temperature_day'] = isset($dailyForecast['main']['temp']) ? $dailyForecast['main']['temp'] : -100.0;
                $dataKey++;
            } else {
                continue;
            }
            if($dataKey > 4) {
                break;
            }
        }

        return $data;
    }

    /**
     * Refactored: Get current weather basic data from openweathermap.org
     * @retturn array Basic weather data
     */
    public function getWeatherForecastDigest() 
    {
        // Get weather forecast data
        $owmData = json_decode(file_get_contents($this->apiForecastUrl), true);

        $todayNo = date('w');
        
        $currentDayNo = 0;
        $currentHighestTemperature = -100.0;
        $currentLowestTemperature = 100.0;

        $dataKey = -1;
        // get timestamps where highest and lowest temepratures occur for each day
        foreach($owmData['list'] as $fcNo => $dailyForecast) {
            
            // day of the week for array index
            $dayNo = date('w', $dailyForecast['dt']);

            // skip forecast for today
            if($dayNo == $todayNo) {
                continue;
            }

            // when day changes set the default criteria and array index
            if($dayNo != $currentDayNo) {
                $currentDayNo = $dayNo;
                $currentHighestTemperature = -100.0;
                $currentLowestTemperature = 100.0;
                $dataKey++;
            }

            // add timestamps for highest temperatures to the $highest array
            if($dailyForecast['main']['temp'] > $currentHighestTemperature) {
                $currentHighestTemperature = $dailyForecast['main']['temp'];
                $highestArr[$dataKey] = $dailyForecast['dt'];
            }

            // add timestamps for lowest temperatures to the $lowest array
            if($dailyForecast['main']['temp'] < $currentLowestTemperature) {
                $currentLowestTemperature = $dailyForecast['main']['temp'];
                $lowestArr[$dataKey] = $dailyForecast['dt'];
            }
        }

        $data = [];
        $dataKey = 0;
        // iterate through main forecast array and scrap data for timestamps for highest/lowest temperatures
        foreach($owmData['list'] as $fcNo => $dailyForecast) {
            
            if(in_array($dailyForecast['dt'], $highestArr)) {
                $data[$dataKey]['day_no'] = date('w', $dailyForecast['dt']);
                $data[$dataKey]['temperature_day'] = isset($dailyForecast['main']['temp']) ? $dailyForecast['main']['temp'] : -100.0;
                $data[$dataKey]['weather_description'] = isset($dailyForecast['weather'][0]['description']) ? $dailyForecast['weather'][0]['description'] : '';
                $data[$dataKey]['weather_icon'] = isset($dailyForecast['weather'][0]['icon']) ? $dailyForecast['weather'][0]['icon'] : '';
                // $tempNightTS = $lowestArr[$dataKey];
                // $data[$dataKey]['temperature_night'] = Stempnight;
                $dataKey++;
            }

            for($i = 0; $i < 40; $i++) {
                if(!isset($lowestArr[$dataKey])) {
                    continue;
                }
                if(isset($owmData['list'][$i]['dt']) && $owmData['list'][$i]['dt'] == $lowestArr[$dataKey]) {
                    $data[$dataKey]['temperature_night'] = isset($owmData['list'][$i]['main']['temp']) ? $owmData['list'][$i]['main']['temp'] : -100.0;
                }
            }

        }
        
        return $data;
    }

    /**
     * Get weather forecast data from openweathermap.org
     * @retturn string Weather forecast data in JSON
     */
    public function getWeatherForecastDigestJSON()
    {
        return json_encode($this->getWeatherForecastDigest(), JSON_FORCE_OBJECT);
    }

    /**
     * Get current weather detailed data (complete last record from the weather_current table)
     */
    public function getWeatherCurrentDetailed()
    {
        // Get weather forecast
        $owmData = json_decode(file_get_contents($this->apiForecastUrl), true);
    }
}