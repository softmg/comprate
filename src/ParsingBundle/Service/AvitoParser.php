<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

use ParsingBundle\Entity\ParsingProductInfo;
use ParsingBundle\Entity\ParsingSite;
use Symfony\Component\DomCrawler\Crawler;

class AvitoParser extends BaseParser
{
    protected $notUseCacheForSearch = false;

    const SEARCH_URL = 'https://www.avito.ru/moskva/tovary_dlya_kompyutera/komplektuyuschie/%s?p=%s&s=1';

    /**
     * Run parsing
     */
    public function run()
    {
        $products = $this->getProductsFromPage('protsessory', 88);

        var_dump($products); exit;
    }

    public function getProductsFromPage($productType, $page)
    {
        $urlForRequest = sprintf(self::SEARCH_URL, $productType, $page);
        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        $products = $crawlerPage->filter('.item')
            ->each(function (Crawler $node) {
                $title = trim($node->filter('h3 a')->first()->text());
                $url = $node->filter('h3 a')->getNode(0)->getAttribute('href');
                $price = preg_replace('/^\s+([\d\s]*?)\s+руб.*?$/s', '$1', $node->filter('.about')->first()->text());
                $price = (int)str_replace(' ', '', $price);

                return new ParsingProductInfo($url, $title, $price);
            });

        return $products;
    }

    public function getParserSiteCode()
    {
        return ParsingSite::AVITO;
    }

    /**
     * @param Crawler $crawler
     * @return Crawler
     */
    public function recognizeAndEnterCaptcha($crawler)
    {
        $captchaCrawler = $crawler->filter('.form__captcha');
        if ($captchaCrawler->count()) {
            $this->dump(" CAPTCHA! Try to recognize captcha");

            $this->addProxyIpCaptcha();

            $this->saveCacheContent('captcha', $this->getCurrentClient()->getResponse()->getContent());
            $captchaText = false;
            if ($this->getRucaptchaToken()) {
                $rucaptcha = new \Rucaptcha\Client($this->getRucaptchaToken());
                $captchaImg = $captchaCrawler->getNode(0)->getAttribute('src');
                $captchaText = $rucaptcha->recognizeFile($captchaImg);
            }

            if ($captchaText) {
                $this->dump(" recognize captcha and try again");

                $form = $crawler->filter('form')->form();
                $crawler = $this->getCurrentClient()->submit($form, array(
                    'rep' => $captchaText
                ));

                $crawler = $this->recognizeAndEnterCaptcha($crawler);
            } else {
                /* set crawler to null if not success captcha */
                $crawler = null;
                $this->dump(" CAPTCHA FAIL!");
            }
        }

        return $crawler;
    }
}
