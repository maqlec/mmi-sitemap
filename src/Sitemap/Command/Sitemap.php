#!/usr/bin/env php
<?php

namespace Sitemap\Command;

//nie ma tu jeszcze autoloadera ładowanie CliAbstract
require_once 'CommandAbstract.php';

/**
 * Generuje sitemapę dla serwisu
 */
class Sitemap extends \Mmi\Command\CommandAbstract
{

    public function run()
    {
        (new \Sitemap\Model\SitemapGenerator(\App\Registry::$config->host))
            ->setFrequency(\App\Registry::$config->sitemap->frequency)
            ->setExclude(\App\Registry::$config->sitemap->exclude)
            ->saveXmlGz(\App\Registry::$config->sitemap->xmlGzPath);
        \Mmi\App\FrontController::getInstance()->getLogger()->info('Sitemap generator finished');
        echo 'Sitemap generator finished';
    }

}

//nowy obiekt
new Sitemap(isset($argv[1]) ? $argv[1] : null);
