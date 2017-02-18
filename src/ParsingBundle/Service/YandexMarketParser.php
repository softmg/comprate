<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

use ParsingBundle\Entity\ParsingSite;
use ProductBundle\Entity\Product;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;

class YandexMarketParser extends BaseParser
{
    const SEARCH_URL = 'https://market.yandex.ru/search.xml?text=%s';
    const PRODUCT_URL = 'https://market.yandex.ru/product/%d/spec';

    /**
     * Run parsing
     */
    public function run()
    {
        $products = $this->getProductsForFirstParsing();

        foreach ($products as $product) {
            $this->getProductAttributes($product);
        }
        var_dump($products); exit;

        //$client = $this->getClient();
        //$crawler = $client->request('GET', 'http://test1.softmg.ru/testip.php');
        $this->getProductUrl('Intel Xeon E3-1290 V2 @ 3.70GHz');
        //$crawlerPage = $this->getCrawlerPage('http://test1.softmg.ru/testip.php');
        var_dump(1);
        exit;
    }

    /**
     * @param Product $product
     */
    public function getProductAttributes($product)
    {
        $productUrl = $this->getProductUrl($product->getName());
        $this->saveProductInfo($product, $productUrl);
        //$domLink = new Link($link, sprintf(self::SEARCH_URL, $product->getName()));
        $link = $this->getCrawler()->selectLink($productUrl)->link();
        $crawler = $this->getGoutteClient()->click($link);

        var_dump($crawler); exit;
    }

    /**
     * @return String
     */
    public function getParserSiteCode()
    {
        return ParsingSite::YANDEX_MARKET;
    }

    /**
     * @param Crawler $crawler
     * @return Crawler
     */
    public function recognizeAndEnterCaptcha($crawler)
    {
        $captchaCrawler = $crawler->filter('.captcha');
        if ($captchaCrawler) {
            $this->dump(" CAPTCHA! Try to recognize captcha");

            $rucaptcha = new \Rucaptcha\Client($this->getRucaptchaToken());
            $captchaText = false;
            //$captchaText = $rucaptcha->recognizeFile('https://i.captcha.yandex.net/image?key=10ixnGFOj1QCQ9ZxeMOnn9p4e3KekF40');

            if ($captchaText) {
                $this->dump(" recognize captcha and try again");

                $form = $crawler->selectButton('sign in')->form();
                $crawler = $this->getGoutteClient()->submit($form, array(
                    'captcha' => $captchaText
                ));
            } else {
                /* set crawler to null if not success captcha */
                $crawler = null;
                $this->dump(" CAPTCHA FAIL!");
            }
        }

        return $crawler;
    }

    /**
    * Get product id by product name on site
    * @param string $productName название товара
    * @throws \Exception
    * @return String
    */
    private function getProductUrl($productName)
    {
        $urlForRequest = sprintf(self::SEARCH_URL, $productName);

        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        if (!$crawlerPage) {
            throw new \Exception("Error to get crawler page from url \"{$urlForRequest}\"");
        }

        $header_link = $crawlerPage->filter('.snippet-card__header-link');
        $yandexProductLink = $header_link->getNode(0);

        return $yandexProductLink->getAttribute('href');
    }
}
