<?php

namespace Sitemap\Model;

/**
 * Klasa reprezentująca element sitemapy
 *
 * @author b.wolos
 *
 * @property string $loc adres url strony
 * @property string $lastmod data modyfikacji
 * @property string $changefreq częstotliwość zmian
 * @property float $priority priorytet
 * @property string $title tytuł strony
 * @property string $description opis strony
 */
class SitemapElement extends \Mmi\DataObject
{

}
