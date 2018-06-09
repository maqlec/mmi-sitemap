<?php

namespace Sitemap\Model;

/**
 * Klasa do generowania sitemapy
 *
 * @author b.wolos
 */
class SitemapGenerator
{

    CONST MAX_SITES = 5000;

    /**
     * Domena serwisu do sparsowania
     * @var string
     */
    private $_domain;

    /**
     * Protokół
     * @var string
     */
    private $_protocol = 'http://';

    /**
     * Częstotliwość zmian w mapie
     * @var string
     */
    private $_frequency;

    /**
     * Domyślny priorytet
     * @var float
     */
    private $_priority;

    /**
     * Adresy do wykluczenia
     * @var array
     */
    private $_exclude = [];

    /**
     * Przetworzone strony
     * @var array
     */
    private $_processedUrls = [];

    /**
     * Znalezione strony do umieszczenia w mapie
     * @var \Sitemap\Model\SitemapElement[]
     */
    private $_sites;

    /**
     * Aktualnie pobrana i przetwarzana strona
     * @var mixed
     */
    private $_currentContent;

    /**
     * Obiekt crawlera do pobierania treści strony
     * @var \Sitemap\Model\Crawler
     */
    private $_crawler;

    /**
     * Konstruktor
     * @param string $domain
     * @param string $protocol
     */
    public function __construct($domain, $protocol = 'http://')
    {
        //wyłączenie limitu czasowego
        set_time_limit(0);
        //domena serwisu
        $this->_domain = $domain;
        //protokół
        $this->_protocol = $protocol;
    }

    /**
     * Ustawia wyrażenia regularne, które mają określić, czy pomijać danego
     * typu odnośniki
     * @param array $exclude
     * @return \Sitemap\Model\SitemapGenerator
     */
    public function setExclude(array $exclude)
    {
        $this->_exclude = $exclude;
        return $this;
    }

    /**
     * Ustawia częstotliwość zmian stron w mapie
     * @param string $frequency
     * @return \Sitemap\Model\SitemapGenerator
     */
    public function setFrequency($frequency = 'daily')
    {
        $this->_frequency = $frequency;
        return $this;
    }

    /**
     * Ustawia priorytet stron w mapie
     * @param string $priority
     * @return \Sitemap\Model\SitemapGenerator
     */
    public function setPriority($priority = 0.5)
    {
        $this->_priority = $priority;
        return $this;
    }

    /**
     * Analizuje strukturę serwisu
     * @return \Sitemap\Model\SitemapGenerator
     */
    private function _crawl()
    {
        //jeśli już zanalizowana struktura
        if ($this->_sites !== null) {
            return $this;
        }
        $this->_sites = new \ArrayObject();
        //startowy url
        $url = $this->_protocol . $this->_domain . '/';
        $this->_getContent($url);
        return $this;
    }

    /**
     * Czy dany URL został już odwiedzony
     * @param string $url
     * @return boolean
     */
    private function _isProcessed($url)
    {
        return in_array($url, $this->_processedUrls);
    }

    /**
     * Ustawia, że dany URL został już odwiedzony
     * @param string $url
     */
    private function _setProcessed($url)
    {
        array_push($this->_processedUrls, $url);
    }

    /**
     * Pobiera zawartość URL
     * @param string $url
     * @return null
     */
    private function _getContent($url)
    {
        if (count($this->_processedUrls) == self::MAX_SITES) {
            return;
        }
        //jeśli już przetworzony
        if ($this->_isProcessed($url)) {
            return;
        }
        //ustawiamy jako przetworzony
        $this->_setProcessed($url);
        $this->_crawler = new Crawler($url, true);
        $this->_crawler->execute();
        if ($this->_crawler->getHttpCode() !== 200) {
            return;
        }
        $this->_currentContent = $this->_crawler->getContent();
        if ($this->_currentContent) {
            //parsujemy zawartość i szukamy linków do kolejnych stron
            $this->_parseContent($url);
        }
    }

    /**
     * Parsuje zawartość strony, wyciąga o niej dane i kolejne linki do analizy
     * @param string $url
     */
    private function _parseContent($url)
    {
        $element = new SitemapElement();
        $element->loc = $url;
        $element->lastmod = date('c');
        $element->changefreq = $this->_frequency;
        $element->priority = $this->_priority;
        $element->title = $this->_findTitle();
        $element->description = $this->_findDescription();
        //wrzucamy do listy znalezionych stron
        $this->_sites->append($element);
        //szukamy kolejnych Urli do analizy
        foreach ($this->_findUrls() as $newUrl) {
            //pobieramy i parsujemy rekurencyjnie kolejny URL
            $this->_getContent($newUrl);
        }
    }

    /**
     * Szuka tytułu strony w aktualnej treści
     * @return string
     */
    private function _findTitle()
    {
        $matches = [];
        if (preg_match('/\<title\>(.*)\<\/title\>/i', $this->_currentContent, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Szuka opisu strony w aktualnej treści
     * @return string
     */
    private function _findDescription()
    {
        $matches = [];
        if (preg_match('/\<meta.+name\=\"description\".+content\=\"(.*)\".*\>/i', $this->_currentContent, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Szuka adresów URL do analizy w aktualnej treści
     * @return array
     */
    private function _findUrls()
    {
        $urls = $matches = [];
        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        if (preg_match_all("/$regexp/siU", $this->_currentContent, $matches)
        && isset($matches[2]) && is_array($matches[2])) {
            foreach ($matches[2] as $href) {
                if (null !== $absoluteUrl = $this->_absoluteUrl($href)) {
                    array_push($urls, $absoluteUrl);
                }
            }
        }
        unset($matches);
        return $urls;
    }

    /**
     * Sprawdza znaleziony link i zwraca jego wersję absolutną
     * @param type $href
     * @return string|null
     */
    private function _absoluteUrl($href)
    {
        //pomijanie pustch i z kotwicą
        if (empty($href) || stripos($href, '#') !== false
        //pomijanie maili, telefonów, itp. oraz wykluczonych
        || preg_match('/^[a-z]+\:.+/i', $href) || $this->_isExcluded($href)) {
            return null;
        }
        //jeśli już absolutny
        if (preg_match('/^http(s)?\:\/\//i', $href)) {
            //jeśli z tej domeny
            if (stripos($href, $this->_domain) !== false) {
                return $href;
            }
            return null;
        }
        if ($href[0] === '/') {
            return $this->_protocol . $this->_domain . $href;
        }
        return $this->_protocol . $this->_domain . '/' . $href;
    }

    /**
     * Czy dany URL zawiera jakieś wyrażenie wykluczające go z analizy
     * @param string $url
     * @return boolean
     */
    private function _isExcluded($url)
    {
        foreach ($this->_exclude as $ex) {
            if (preg_match("$ex", $url)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generuje mapę w formacie Xml i zapisuje w wybranym miejscu na dysku
     * @param string $path
     * @return boolean
     */
    public function saveXml($path)
    {
        //analiza struktury
        $this->_crawl();
        //zapis do Xml
        return (new XmlRenderer($this->_sites))->save($path);
    }

    /**
     * Generuje skompresowaną mapę w formacie Xml i zapisuje na dysku
     * @param string $path
     * @return boolean
     */
    public function saveXmlGz($path)
    {
        //analiza struktury
        $this->_crawl();
        //zapis do Xml Gz
        return (new XmlRenderer($this->_sites))->saveGz($path);
    }

}
