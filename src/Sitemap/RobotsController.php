<?php

namespace Sitemap;

/**
 * Kontroler modułu Sitemap/Robots
 * (Odpowiada na request robots.txt)
 */
class RobotsController extends \Mmi\Mvc\Controller
{
    /**
     * @return string
     */
    public function robotsAction()
    {
        $this->view->setLayoutDisabled();
        if (\App\Registry::$config->host != 'www.nowaera.pl') {
            return nl2br("User-agent: *\nDisallow: /");
        }
        return nl2br("User-agent: *\nAllow: /");
    }

}
