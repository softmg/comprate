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

class BenchmarkSearchParser extends BaseParser
{
    protected $productType;

    const SEARCH_URL = 'https://www.passmark.com/search/zoomsearch.php?zoom_sort=0&zoom_xml=0&zoom_query=<searchText>&zoom_per_page=10&zoom_and=1&zoom_cat%5B%5D=5';
    const PRODUCTS_PAGE_TYPE = [
        ProductType::CPU => 'http://www.cpubenchmark.net/cpu.php',
        ProductType::VIDEOCARD => 'http://www.videocardbenchmark.net/gpu.php',
        ProductType::STORAGE => 'http://www.harddrivebenchmark.net/hdd.php',
        ProductType::MEMORY => 'http://www.memorybenchmark.net/ram.php',
        ProductType::MOBILE_ANDROID => 'http://www.androidbenchmark.net/phone.php',
        ProductType::MOBILE_IOS => 'http://www.iphonebenchmark.net/phone.php',
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
        $productRepo = $productTypeRepo = $this->em->getRepository('ProductBundle:Product');
        $product = $productRepo->find('3982');

        $this->findProduct($product);
    }

    /**
     * @param Product $product
     * @return mixed
     */
    protected function findProduct($product)
    {
        $offerRepo = $this->em->getRepository('ProductBundle:Offer');
        $offer = $offerRepo->find(6);
        $offer->setIsFail(1);
        $this->em->persist($offer);
        $this->em->flush();
        var_dump(count(1)); exit;
        $crawlerPage = $this->getCrawlerPage($this->getSerchUrl($product->getName()));

        $results = $crawlerPage->filter('.result_title a');

        if ($results) {
            foreach ($results as $result) {
                $productUrl = $result->getNode(0)->getAttribute('href');
                if ($productUrl) {
                    $crawlerPage = $this->getCrawlerPage($productUrl);


                }
            }
        }
    }

    private function getSerchUrl($productName)
    {
        return str_replace('<searchText>', $productName, self::SEARCH_URL);
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
