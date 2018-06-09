<?php

namespace Sitemap\Model;

/**
 * Klasa do pobierania treści stron
 *
 * @author b.wolos
 */
class Crawler
{

    /**
     * Adres URL strony do pobrania
     * @var string
     */
    private $_url;

    /**
     * Czy pobrać info o stronie
     * @var boolean
     */
    private $_withInfo = false;

    /**
     * Pobrana zawartość
     * @var string
     */
    private $_content;

    /**
     * Dodatkowe info o stronie
     * @var array
     */
    private $_info = [];

    /**
     * Konstruktor
     * @param string $url
     * @param boolean $withInfo
     */
    public function __construct($url, $withInfo = false)
    {
        //url
        $this->_url = $url;
        //czy dodatkowe info
        $this->_withInfo = $withInfo;
    }

    /**
     * Wykonuje operację pobrania treści z URL
     * @return \Sitemap\Model\Crawler
     */
    public function execute()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->_url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, $this->_withInfo);
        $this->_content = curl_exec($curl);
        if ($this->_withInfo) {
            $this->_info = curl_getinfo($curl);
        }
        curl_close($curl);
        return $this;
    }

    /**
     * Zwraca zawartość strony
     * @return string|false
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Zwraca dodatkowe info o stronie
     * @return array
     */
    public function getInfo()
    {
        return $this->_info;
    }

    /**
     * Zwraca dodatkowe info o stronie po kluczu
     * @param string $key
     * @return mixed
     */
    public function getInfoByKey($key)
    {
        if (array_key_exists($key, $this->_info)) {
            return $this->_info[$key];
        }
        return null;
    }

    /**
     * Zwraca kod odpowiedzi HTTP
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->getInfoByKey('http_code');
    }

}
