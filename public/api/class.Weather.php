<?php
/**
 * Top level weather class 
 */
abstract class Weather
{
    protected $baseDir = '';
    protected $apiCurrentUrl = null;
    protected $apiForecastUrl = null;

    public function __construct() {
        $this->baseDir = dirname(__DIR__, 2);
    }
 
    // Required: method to return a digest for current weather
    abstract public function getWeatherCurrentDigest();

    // Required: a method to return digest forecast
    abstract public function getWeatherForecastDigest();
}