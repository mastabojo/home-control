<?php
/*
  Weather current data and forecast API
  Data read form opeweathermap.org API
*/

class Weather
{
    protected $apiCurrentUrl = null;
    protected $apiForecastUrl = null;
    protected $weather = [];
    protected $forecast = [];

    public function __construct() {

        include dirname(__DIR__) . '/env.php';

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
        
        // $data['url'] = $this->apiCurrentUrl;
        // $data['DEBUG'] = dirname(__DIR__) . '/env.php';

        return $data;
        // return $owmData;
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