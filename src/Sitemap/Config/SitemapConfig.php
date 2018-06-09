<?php

namespace Sitemap\Config;

/**
 * Konfiguracja sitemapy
 */
class SitemapConfig
{

    /**
     * Częstotliwość zmian stron
     * @var string
     */
    public $frequency = 'daily';

    /**
     * Ścieżka zapisu mapy skompresowanej Xml
     * @var string
     */
    public $xmlGzPath = BASE_PATH . '/web/data/sitemap.xml.gz';

    /**
     * Wzorce do wykluczania linków
     * @var array
     */
    public $exclude = [
        '/pdf\/([a-zA-Z0-9\-\/]+)$/',
        '/drukuj\/([a-zA-Z0-9\-\/]+)$/',
        '/render\/([a-zA-Z0-9\-\/]+)$/',
        '/\/data\/([a-z0-9]+\/){4}/',
        '/module\=file/',
    ];

}
