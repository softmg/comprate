<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseFreeProxyParser extends BaseParser
{
    /**
     * @param Crawler $crawler
     * @return Crawler
     */
    public function recognizeAndEnterCaptcha($crawler)
    {
        return $crawler;
    }

    protected function getProxyType($text)
    {
        if (strpos(strtolower($text), 'https') !== false) {
            $proxyType = 'https';
        } elseif (strpos(strtolower($text), 'http') !== false) {
            $proxyType = 'http';
        } else {
            $proxyType = $text;
        }

        return $proxyType;
    }
}
