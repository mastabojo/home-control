<?php
/**
 * 
 */

class Lang
{
    protected $language = 'en';
    protected $langStrings = [];

    public function __construct($language = null)
    {
        if($language != null) {
            $this->language = $language;
        }
        $this->setLangStrings();
    }

    public function setLangStrings()
    {
        $basePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
        include_once $basePath . "lib/lang/lang.{$this->language}.php";
        $this->langStrings = $lang;
    }

    /**
     * Return a string by a key in selected language
     * If the key does not exist return - 
     * Language is set in env.php 
     */
    public function Get($key, $subkey = null)
    {
        if(isset($this->langStrings[$key])) {
            return $subkey === null ? $this->langStrings[$key] : $this->langStrings[$key][$subkey];
        } else {
            return '-';
        }
    }
}
