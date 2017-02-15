<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

use ParsingBundle\Entity\ParsingSite;

class YandexMarketParser extends BaseParser
{
    const SEARCH_URL = 'https://market.yandex.ru/search.xml?text=%s';
    const PRODUCT_URL = 'https://market.yandex.ru/product/%d/spec';

    /**
     * Run parsing
     */
    public function run()
    {
        $site = $this->getParserSiteAndCheck();

       
    }

    /**
     * @return String
     */
    public function getParserSiteCode()
    {
        return ParsingSite::YANDEX_MARKET;
    }

    /**
    * Get product id by product name on site
    * @param string $productName название товара
    * @throws \Exception
    * @return bool|int
    */
    private function getProductId($productName)
    {
        $result = false;
        $urlForRequest = sprintf(self::SEARCH_URL, $productName);

        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        if (!$crawlerPage) {
            throw new \Exception("Error to get crawler page from url \"{$urlForRequest}\"");
        }

        $header_link = $crawlerPage->filter('.snippet-card__header-link');
        var_dump($header_link); exit;
        if ($header_link) {
            $checkUrl = $header_link->href;
            $parseUrl = parse_url($checkUrl);
        }


        if (isset($parseUrl['path'])) {
            $output = explode('product/', $parseUrl['path']);
            if (isset($output[1]) && is_numeric($output[1])) {
                $result = $output[1];
            }
        }


        return $result;
    }
}
