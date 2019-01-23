<?php
/**
 * Top level weather class 
 */
abstract class Weather
{
    protected $apiCurrentUrl = null;
    protected $apiForecastUrl = null;

    public function __consruct()
    {

    }

    // Required: method to return a digest for current weather
    abstract public function getWeatherCurrentDigest();

    // Required: a method to return digest forecast
    abstract public function getWeatherForecastDigest();
}