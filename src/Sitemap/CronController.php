<?php

namespace Sitemap;

/**
 * Kontroler zadań generowania sitemapy
 */
class CronController extends \Mmi\Mvc\Controller
{
    CONST INFO = 'Sitemap generator finished';

    /**
     * Akcja generowania sitemapy
     */
    public function generateSitemapAction()
    {
        (new Model\SitemapGenerator(\App\Registry::$config->host))
            ->setFrequency(\App\Registry::$config->sitemap->frequency)
            ->setExclude(\App\Registry::$config->sitemap->exclude)
            ->saveXmlGz(\App\Registry::$config->sitemap->xmlGzPath);
        $this->getLogger()->info(self::INFO);
        return self::INFO;
    }

}
