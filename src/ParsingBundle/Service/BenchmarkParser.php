<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

use ParsingBundle\Entity\ParsingSite;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\ProductType;
use Symfony\Component\DomCrawler\Crawler;

class BenchmarkParser extends BaseParser
{
    protected $productType;

    const PARSER_PAGES = [
        ProductType::MOBILE_ANDROID => 'http://www.androidbenchmark.net/passmark_chart.html',
        ProductType::MOBILE_IOS => 'http://www.iphonebenchmark.net/passmark_chart.html',
    ];

    /**
     * @return String
     */
    public function getParserSiteCode()
    {
        return ParsingSite::BENCHMARK;
    }
    
    /**
     * Run parsing
     */
    public function run()
    {
        $this->productType = ProductType::MOBILE_ANDROID;

        $this->parsePage($this->getSearchUrl());
    }
    
    protected function getSearchUrl()
    {
        return self::PARSER_PAGES[$this->productType];
    }

    /**
     * @param String $urlRequest
     */
    private function parsePage($urlRequest)
    {
        $crawlerPage = $this->getPage($urlRequest);

        $this->addProductsFromPage($crawlerPage);
    }

    /**
     * @param Crawler
     */
    private function addProductsFromPage($crawlerPage)
    {
        $currentUrlInfo = parse_url($this->getSearchUrl());
        $currentHost = $currentUrlInfo['scheme'] . '://' . $currentUrlInfo['host'];

        $crawlerPage->filter('table.chart tr')->each(function ($node) use ($currentHost) {
            $mobileLink = $node->filter('a');
            if ($mobileLink->count()) {
                $productName = $mobileLink->text();
                $productUrl = $currentHost . '/' . $mobileLink->getNode(0)->getAttribute('href');
                if (strpos($productUrl, '/phone') !== false) {
                    $productRate = $node->filter('td.value')->getNode(0)->textContent;
                    $productRate = preg_replace('/[^\d]/s', '', $productRate);
                    $product = $this->addProduct($productName, $this->productType);
                    if ($productRate) {
                        $product->setRate($productRate);
                        $this->em->persist($product);
                        $this->em->flush();
                    }
                    if ($product) {
                        $this->saveProduct($product, $productUrl);
                    }
                }
            }
        });
    }

    /**
     * @param String $urlForRequest
     * @return Crawler
     */
    private function getPage($urlForRequest)
    {
        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        return $crawlerPage;
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
