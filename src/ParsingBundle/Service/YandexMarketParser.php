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

class YandexMarketParser extends BaseParser
{
    const SEARCH_URL = 'https://market.yandex.ru/search.xml?text=%s';
    const PRODUCT_URL = 'https://market.yandex.ru/product/%d/spec';

    /**
     * Run parsing
     */
    public function run()
    {
        $this->moveCacheToSubDirs();
        
        exit;
        //$crawler = $this->getCacheCrawler('captcha');
        //$captchaImg = $crawler->filter('.form__captcha')->getNode(0)->getAttribute('src');
        //var_dump($captchaImg); exit;
        //$crawlerPage = $this->getCrawlerPage('http://test1.softmg.ru/testip.php', true, true);
        //var_dump($this->getGoutteClient()->getResponse()->getContent()); exit;
        //$rucaptcha = new \Rucaptcha\Client($this->getRucaptchaToken());
        //$captchaText = false;
        //$captchaText = $rucaptcha->recognizeFile('https://na.captcha.yandex.net/image?key=c1FSkolaNoVoM59xayyxfFXaJmQb6cUo');
        //var_dump($captchaText);
//exit;
        $products = $this->getProductsForFirstParsing();

        foreach ($products as $product) {
            $this->getProductAttributes($product);
        }
    }

    protected function getCharacteristicUrl($productUrl, $productId)
    {
        $urlForRequest = false;

        /* three page chain like human */
        if ($this->isFromCache() && $productUrl) {
            $urlForRequest = $this->getParserSite()->getUrl() . $productUrl;
            $crawlerPage = $this->getCrawlerPage($urlForRequest);

            $charactersiticLink = $crawlerPage->selectLink('Характеристики');
            if ($charactersiticLink->count()) {
                $urlForRequest = $charactersiticLink->getNode(0)->getAttribute('href');
            } else {
                $this->dump(" Can not get charachteristics link $urlForRequest");
            }
        }

        if (!$this->isFromCache()) {
            $urlForRequest = sprintf(self::PRODUCT_URL, $productId);
        }

        return $urlForRequest;
    }

    /**
     * @param Product $product
     * @throws \Exception
     */
    public function getProductAttributes($product)
    {
        list($productUrl, $productId) = $this->getProductUrlAndId($product->getName());

        if (strpos($productUrl, 'redir') !== false || !$productUrl) {
            /* save failed results too */
            $this->saveProductInfo($product, $this->getParserSite()->getUrl() . $productUrl, true);

            return;
        }

        $urlForRequest = $this->getCharacteristicUrl($productUrl, $productId);

        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        $attributes = $crawlerPage->filter('.product-spec__name-inner')->each(function ($node) {
            $text = $node->text();
            $attribute = strpos($text, '?') ? substr($node->text(), 0, strpos($text, '?')) : $text;
            return trim($attribute);
        });
        $attributesValues =  $crawlerPage->filter('.product-spec__value-inner')->each(function ($node) {
            $text = $node->text();
            return trim($text);
        });

        if (count($attributes)) {
            if (count($attributes) !== count($attributesValues)) {
                throw new \Exception('count attributes and attributes names different!');
            }
            foreach ($attributes as $num => $attributeName) {
                $this->addAttributeToProduct($product, $attributeName, $attributesValues[$num]);
            }
        } else {
            $this->dump(" Does not get attributes from page $urlForRequest");
        }

        $this->saveProductInfo($product, $this->getParserSite()->getUrl() . $productUrl);
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
        $captchaCrawler = $crawler->filter('.form__captcha');
        if ($captchaCrawler->count()) {
            $this->dump(" CAPTCHA! Try to recognize captcha");
            
            $this->addProxyIpCaptcha();

            $this->saveCacheContent('captcha', $this->getGoutteClient()->getResponse()->getContent());
            $captchaText = false;
            if ($this->getRucaptchaToken()) {
                $rucaptcha = new \Rucaptcha\Client($this->getRucaptchaToken());
                $captchaImg = $captchaCrawler->getNode(0)->getAttribute('src');
                $captchaText = $rucaptcha->recognizeFile($captchaImg);
            }

            if ($captchaText) {
                $this->dump(" recognize captcha and try again");

                $form = $crawler->filter('form')->form();
                $crawler = $this->getGoutteClient()->submit($form, array(
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

    /**
    * Get product id by product name on site
    * @param string $productName название товара
    * @throws \Exception
    * @return String
    */
    private function getProductUrlAndId($productName)
    {
        $productId = false;
        $yandexProductUrl = false;

        $urlForRequest = sprintf(self::SEARCH_URL, $productName);

        /* get new client and proxy ip */
        $this->getClient();

        if (!$this->hasCookie('yandex_gid')) {
            $this->getCrawlerPage('https://yandex.ru/', false, true);
            $this->dump(" go to yandex to set cookie for ip: {$this->getProxyIp()->getIp()}");
        }

        if (!$this->isFileExistInCache($urlForRequest)) {
            /* in first go to main site page */
            $this->getCrawlerPage($this->getParserSite()->getUrl(), false, true);
        }

        /* product list with new client */
        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        $header_link = $crawlerPage->filter('.snippet-card__header-link');
        if ($header_link->count()) {
            $yandexProductUrl = $header_link->getNode(0)->getAttribute('href');
            if (strpos($yandexProductUrl, 'redir') === false) {
                $yandexParseUrl = parse_url($yandexProductUrl);
                if ($yandexParseUrl && isset($yandexParseUrl['path'])) {
                    $productId = preg_replace('/[^\d]/s', '', $yandexParseUrl['path']);
                } else {
                    $this->dump(" ERROR: can not get product url for product name '$productName' and url '$yandexProductUrl'");
                }
            } else {
                $this->dump(" ERROR: detected redirect url for product '$productName' and url '$yandexProductUrl'");
            }
        } else {
            $this->dump(" ERROR: not found product '$productName' on url '$urlForRequest'");
        }

        return [$yandexProductUrl, $productId];
    }
}
