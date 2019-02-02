<?php
/*
  Weather current data and forecast API
  Data read from ARSO API
*/
include(dirname(__DIR__) . '/api/class.Weather.php');
class WeatherARSO extends Weather
{
    public function __construct() {

        include dirname(__DIR__) . '/env.php';

        $this->apiCurrentUrl  = "http://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observation_LJUBL-ANA_BEZIGRAD_latest.xml";
        $this->apiForecastUrl = "http://meteo.arso.gov.si/uploads/probase/www/fproduct/text/sl/forecast_SI_OSREDNJESLOVENSKA_latest.xml";
    }

    /**
     * Get current weather basic data
     * @retturn array Basic weather data
     */
    public function getWeatherCurrentDigest() 
    {


        // Get weather data
        $arsoData = new SimpleXMLElement(file_get_contents($this->apiCurrentUrl));

        // die(print_r($arsoData, 1) . "\n");
        // die('-- ' . $arsoData->metData->tsUpdated . "\n");

    
        $data = [];
        $data['calc_time']           = isset($arsoData->metData->tsUpdated) ? strtotime((string) $arsoData->metData->tsUpdated) : -1;
        // $data['calc_time']        = isset($arsoData->metData->tsUpdated_UTC) ? date_create_from_format('Y-m-d H:i:s', (string) $arsoData->metData->tsUpdated_UTC) : -1;
        $data['city_id']             = -1;
        $data['city_name']           = isset($arsoData->metData->domain_longTitle) ? (string) $arsoData->metData->domain_longTitle : '';
        $data['weather_id']          = -1;
        $data['weather_main']        = '';
        $data['weather_description'] = isset($arsoData->metData->nn_shortText) ? (string) $arsoData->metData->nn_shortText : '';
        $data['weather_icon']        = isset($arsoData->metData->nn_icon) ? $this->getWeatherIcon((string) $arsoData->metData->nn_icon) : '';
        $data['temperature']         = isset($arsoData->metData->t_degreesC) ? (float) $arsoData->metData->t_degreesC + 10.0 : -100.0;
        $data['pressure']            = isset($arsoData->metData->p) ? (integer) $arsoData->metData->p : -1;
        $data['humidity']            = isset($arsoData->metData->rh) ? (int) $arsoData->metData->rh : -1;
        $data['wind_speed']          = isset($arsoData->metData->ff_val_kmh) ? (int) $arsoData->metData->ff_val_kmh : -1;
        $data['wind_direction']      = isset($arsoData->metData->dd_val) ? (int) $arsoData->metData->dd_val : -1;
        $data['clouds_all']          = -1;
        $data['rain_1h']             = isset($arsoData->metData->rr24h_val) ? (int) $arsoData->metData->rr24h_val : -1;
        $data['rain_3h']             = isset($arsoData->metData->rr24h_val) ? (int) $arsoData->metData->rr24h_val : -1;
        $data['snow_1h']             = isset($arsoData->metData->snow) ? (int) $arsoData->metData->snow : -1;
        $data['snow_3h']             = isset($arsoData->metData->snow) ? (int) $arsoData->metData->snow : -1;
        $data['sunrise']             = isset($arsoData->metData->sunrise) ? strtotime((string) $arsoData->metData->sunrise) : -1;
        $data['sunset']              = isset($arsoData->metData->sunset) ? strtotime((string) $arsoData->metData->sunset) : -1;

        // print_r(json_decode($arsoData, true));

        return $data;
    }

    /**
     * Get current weather basic data
     * @retturn string Basic weather data in JSON
     */
    public function getWeatherCurrentDigestJSON() 
    {
        return json_encode($this->getWeatherCurrentDigest(), JSON_FORCE_OBJECT);
    }

    
    /**
     * Refactored: Get current weather basic data from openweathermap.org
     * @retturn array Basic weather data
     */
    public function getWeatherForecastDigest() 
    {
        // Get weather forecast data
  
        $data = [];

        
        return $data;
    }

    /**
     * Get weather forecast data 
     * @retturn string Weather forecast data in JSON
     */
    public function getWeatherForecastDigestJSON()
    {
        return json_encode($this->getWeatherForecastDigest(), JSON_FORCE_OBJECT);
    }

    /**
     * Get current weather detailed data 
     */
    public function getWeatherCurrentDetailed()
    {
        // Get weather forecast
        $data = json_decode(file_get_contents($this->apiForecastUrl), true);
    }

    protected function getWeatherIcon($name, $ts = null) {
        
        // day or night icon
        $hour = $ts == null ? date('H') : date('H', $ts);
        $dayPart = $hour >= 5 && $hour < 17 ? 'd' : 'n';

        // Weather icons mapping from ARSO to OWM (used in this application)
        $iconNames = [
            'clear'      => '01' . $dayPart,
            'mostClear'  => '01' . $dayPart, 
            'partCloudy' => '02' . $dayPart,
            'modCloudy'  => '02' . $dayPart,
            'prevCloudy' => '03' . $dayPart,
            'overcast'   => '04' . $dayPart,
            'FG'         => '50' . $dayPart, // fog
            'RA'         => '09' . $dayPart, // rain
            'DZ'         => '09' . $dayPart, // drizzle
            'SN'         => '13' . $dayPart, // snow
            'RASN'       => '13' . $dayPart, // rain with snow
            'TS'         => '11' . $dayPart, // thunderstorm
            'TSGR'       => '13' . $dayPart, // TSGR (TS with hail)
            'lightRA'    => '09' . $dayPart,
            'modRA'      => '09' . $dayPart,
            'heavyRA'    => '10' . $dayPart,
            'lightDZ'    => '09' . $dayPart,
            'modDZ'      => '09' . $dayPart,
            'heavyDZ'    => '09' . $dayPart,       
            'lightSN'    => '13' . $dayPart,
            'modSN'      => '13' . $dayPart,
            'heavySN'    => '13' . $dayPart
        ];

        return $iconNames[$name]; 
    }
}