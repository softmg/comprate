<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

use ParsingBundle\Entity\ParsingSite;
use Symfony\Component\DomCrawler\Crawler;

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

        //$client = $this->getClient();
        //$crawler = $client->request('GET', 'http://test1.softmg.ru/testip.php');
        $this->getProductId('Intel Xeon E3-1290 V2 @ 3.70GHz');
        //$crawlerPage = $this->getCrawlerPage('http://test1.softmg.ru/testip.php');
        var_dump(1);
        exit;
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
            if (isset($output[1]) && is_numeric($output[1])) {
                $result = $output[1];
            }
        }


        return $result;
    }
}
