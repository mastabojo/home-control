<?php
/*
  Weather current data and forecast API
  Data read from ARSO API
*/
include(dirname(__DIR__) . '/api/class.Weather.php');
class WeatherARSO extends Weather
{
    public function __construct() 
    {
        include dirname(__DIR__, 2) . '/env.php';

        $this->apiCurrentUrl  = "http://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observation_LJUBL-ANA_BEZIGRAD_latest.xml";
        $this->apiForecastUrl = "http://meteo.arso.gov.si/uploads/probase/www/fproduct/text/sl/forecast_SI_OSREDNJESLOVENSKA_latest.xml";
    }

    /**
     * Get current weather basic data
     * @retturn array Basic weather data
     */
    public function getWeatherCurrentDigest() 
    {
        if(!$this->isUrlReachable($this->apiCurrentUrl)) {
            return [];
        }

        // Get weather data
        // $arsoData = new SimpleXMLElement(file_get_contents($this->apiCurrentUrl));
        $arsoData = new SimpleXMLElement($this->apiCurrentUrl, 0, true);

        $data = [];
        $data['calc_time']           = isset($arsoData->metData->tsUpdated) ? strtotime((string) $arsoData->metData->tsUpdated) : -1;
        $data['city_id']             = -1;
        $data['city_name']           = isset($arsoData->metData->domain_longTitle) ? (string) $arsoData->metData->domain_longTitle : '';
        $data['weather_id']          = -1;
        $data['weather_main']        = '';
        $data['weather_description'] = isset($arsoData->metData->nn_shortText) ? (string) $arsoData->metData->nn_shortText : '';
        $data['weather_icon']        = isset($arsoData->metData->nn_icon) ? $this->getWeatherIcon((string) $arsoData->metData->nn_icon) : 
                                       (isset($arsoData->metData->wwsyn_icon) ? $arsoData->metData->wwsyn_icon : '');
        $data['temperature']         = isset($arsoData->metData->t_degreesC) ? (float) $arsoData->metData->t_degreesC : -100.0;
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

        return $data;
    }

    /**
     * Get current weather basic data
     * @retturn string Basic weather data in JSON
     */
    public function getWeatherCurrentDigestJSON() 
    {
        return json_encode($this->getWeatherCurrentDigest(), JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Get weather forecast data from ARSO (arso.gov.si)
     * @retturn array Weather forecast data
     */
    public function getWeatherForecastDigest() 
    {
        if(!$this->isUrlReachable($this->apiForecastUrl)) {
            return [];
        }
        // Get weather forecast data
        $arsoData = new SimpleXMLElement($this->apiForecastUrl, 0, true);

        $data = [];
        $dataKey = -1;

        foreach($arsoData->metData as $key => $metData) {

            // skip current day
            if($dataKey == -1) {
                $dataKey++;
                continue;
            }

            $validDate = new DateTime((string) $metData->valid_UTC);

            $data[$dataKey]['day_no'] = $validDate->format("N");
            $data[$dataKey]['temperature_day'] = (string) $metData->txsyn;
            $data[$dataKey]['temperature_night'] = (string) $metData->tnsyn;
            $data[$dataKey]['weather_description'] = (string) $metData->nn_shortText;
            $data[$dataKey]['weather_icon'] = $this->getWeatherIcon((string) $metData->nn_icon, $validDate->getTimestamp());
            $dataKey++;
        }

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
        // Get current weather
        return "...";

    }

    protected function getWeatherIcon($name, $ts = null) 
    {
        if($name == '') {
            return '00dn'; // no icon
        }
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

        return isset($iconNames[$name]) ? $iconNames[$name] : '00dn'; 
    }

    /**
     * Check if URL is available
     * 
     * @param string URL to check
     * @return bool true if reachable false otherwise
     */
    protected function isUrlReachable($url)
    {
        // Temporary disabled
        // return true;

        $resURL = curl_init(); 
        curl_setopt($resURL, CURLOPT_URL, $url); 
        curl_setopt($resURL, CURLOPT_BINARYTRANSFER, 1); 
        // curl_setopt($resURL, CURLOPT_HEADERFUNCTION, 'curlHeaderCallback'); 
        curl_setopt($resURL, CURLOPT_FAILONERROR, 1); 
        curl_exec ($resURL); 
        $intReturnCode = curl_getinfo($resURL, CURLINFO_HTTP_CODE); 
        curl_close ($resURL); 
        if ($intReturnCode == 200 || $intReturnCode == 302 || $intReturnCode == 304) { 
            return true; 
        } else {
            return false;
        }
    }
}
