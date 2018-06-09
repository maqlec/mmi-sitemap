<?php

namespace Sitemap\Model;

/**
 * Klasa renderera sitempay do formatu Xml
 *
 * @author b.wolos
 */
class XmlRenderer
{

    /**
     * Mapa do wyrenderowania
     * @var \Sitemap\Model\SitemapElement[]
     */
    private $_map;

    /**
     * Korzeń budowanego dokumentu Xml
     * @var \SimpleXMLElement
     */
    private $_simpleXmlElement;

    /**
     * Konstruktor
     * @param \ArrayObject $map
     */
    public function __construct(\ArrayObject $map)
    {
        $this->_map = $map;
    }

    /**
     * Renderuje mapę
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
    }

    /**
     * Renderuje mapę
     * @return string
     */
    public function asString()
    {
        return $this->_buildXml()->asXML();
    }

    /**
     * Zapisuje plik Xml z mapą na dysku
     * @param string $filename
     * @return boolean
     */
    public function save($filename)
    {
        return $this->_buildXml()->asXML($filename);
    }

    /**
     * Zapisuje skompresowany plik Xml (GZ)
     * @param string $filename
     * @return boolean
     */
    public function saveGz($filename)
    {
        if (false === $gzHandle = gzopen($filename, 'w9')) {
            return false;
        }
        gzwrite($gzHandle, $this->_buildXml()->asXML());
        return gzclose($gzHandle);
    }

    /**
     * Buduje strukturę dokumentu Xml
     * @return \SimpleXMLElement
     */
    private function _buildXml()
    {
        if ($this->_simpleXmlElement instanceof \SimpleXMLElement) {
            return $this->_simpleXmlElement;
        }
        //budowanie struktury Xml
        $this->_simpleXmlElement = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"></urlset>');
        //dla każdego elementu
        foreach ($this->_map as $element) {
            $urlTag = $this->_simpleXmlElement->addChild('url');
            $urlTag->addChild('loc', htmlspecialchars($element->loc));
            if ($element->lastmod) {
                $urlTag->addChild('lastmod', $element->lastmod);
            }
            if ($element->changefreq) {
                $urlTag->addChild('changefreq', $element->changefreq);
            }
            if ($element->priority) {
                $urlTag->addChild('priority', $element->priority);
            }
        }
        return $this->_simpleXmlElement;
    }

}
