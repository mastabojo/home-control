<?php
/*
  Weather current data and forecast API
  Data read form database
*/

// include_once dirname(__DIR__) . '/env.php';
// include_once dirname(__DIR__) . '/functions.php';

class WeatherDB
{
    protected $DB = null;

    public function __construct() {
        include_once dirname(__DIR__) . '/env.php';
        try {
            $this->DB = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES  => false]);
        } catch (PDOException $e) {
            // throw new PDOException($e->getMessage(), (int)$e->getCode());
            error_log("Could not connect ot database $DB_NAME");
            $this->DB = null;
        }
    }

    public function __destruct() 
    {
        unset($this->DB);
    }

    /**
     * Get current weather basic data (selected fields fom last record from the weather_current table)
     */
    public function getWeatherCurrentDigest() 
    {
        if(!isset($this->DB) || $this->DB == null) {
            error_log("No connection to the database");
            return false;
        }
        $fields = ['calc_time', 'city_id', 'weather_main', 'weather_icon', 'temperature', 'pressure', 'humidity', 'wind_speed', 'wind_direction'];
        $q = 'SELECT ' . implode(', ', $fields) . ' FROM weather_current ORDER BY id DESC LIMIT 1';
        $stmt = $this->DB->prepare($q);
        try {
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
            error_log('Could not get weather current data: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get current weather detailed data (complete last record from the weather_current table)
     */
    public function getWeatherCurrentDetailed()
    {

    }
}