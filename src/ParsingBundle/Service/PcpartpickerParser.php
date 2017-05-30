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

class PcpartpickerParser extends BaseParser
{
    protected $numPages = false;
    protected $productType;

    const SEARCH_URL = 'https://pcpartpicker.com/products/%s/fetch/?page=%s';

    /**
     * @return String
     */
    public function getParserSiteCode()
    {
        return ParsingSite::PCPARTPICKER;
    }
    
    /**
     * Run parsing
     */
    public function run()
    {
        $this->productType = ProductType::CPU;
        

        $startPage = 1;
        $this->parsePage($startPage);

        for ($i = $startPage + 1; $i <= $this->numPages; $i++) {
            $this->parsePage($i);
        }
    }
    
    protected function getSearchUrl($page)
    {
        return sprintf(self::SEARCH_URL, $this->productType, $page);
    }

    /**
     * @param Crawler
     * @return Int $pageNum
     */
    private function getPageNumber($crawlerPage)
    {
        $pageNum = 1;
        
        $response = json_decode($crawlerPage->getNode(0)->textContent);

        if (preg_match('/·([\d]+?)$/s', $response->result->paging_row, $matches)) {
            $pageNum = (int)$matches[1];
        }
        
        return $pageNum;
    }

    /**
     * @param Int $pageNum
     */
    private function parsePage($pageNum)
    {
        $crawlerPage = $this->getPage($pageNum);

        if (!$this->numPages) {
            $this->numPages = $this->getPageNumber($crawlerPage);
        }

        $this->addProductsFromPage($crawlerPage);
    }

    /**
     * @param Crawler
     */
    private function addProductsFromPage($crawlerPage)
    {
        $response = json_decode($this->getResponse());
        $crawlerPage = new Crawler();
        $crawlerPage->addHtmlContent($response->result->html);

        $crawlerPage->filter('a')->each(function ($node) {
            $productUrl= $node->getNode(0)->getAttribute('href');
            if (strpos($productUrl, '/product/') !== false) {
                $product = $this->addProduct($node->text(), $this->productType, true);
                if ($product) {
                    $this->saveProduct($product, $productUrl, true);
                }
            }
        });
    }

    /**
     * @param Int $pageNum
     * @return Crawler
     */
    private function getPage($pageNum)
    {
        $urlForRequest = $this->getSearchUrl($pageNum);
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
