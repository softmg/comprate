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
            $urlForRequest = $this->clearUrl($productUrl);
            $crawlerPage = $this->getCrawlerPage($urlForRequest);

            $charactersiticLink = $crawlerPage->selectLink('Характеристики');
            if ($charactersiticLink->count()) {
                $urlForRequest = $charactersiticLink->getNode(0)->getAttribute('href');
                $urlForRequest = $this->clearUrl($urlForRequest);
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
            $this->saveProductInfo($product, $productUrl, true);

            return;
        }

        $urlForRequest = $this->getCharacteristicUrl($productUrl, $productId);
        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        $this->parseProductCharacteristicPage($product, $crawlerPage, $urlForRequest);

        $this->saveProductInfo($product, $productUrl);
    }

    /**
     * Parse page with product characteristics
     * @param Product $product
     * @param Crawler
     * @param String
     * @throws \Exception
     * @return Bool
     */
    protected function parseProductCharacteristicPage($product, $productPageCrawler, $urlForRequest)
    {
        $attributes = [];
        $attributesValues = [];

        if ($productPageCrawler->filter('.product-spec__name-inner')->count()) {
            $attributes = $productPageCrawler->filter('.product-spec__name-inner')
                ->each(function ($node) {
                    $text = $node->text();
                    $attribute = strpos($text, '?') ? substr($node->text(), 0, strpos($text, '?')) : $text;

                    return trim($attribute);
                });
            $attributesValues = $productPageCrawler->filter('.product-spec__value-inner')
                ->each(function ($node) {
                    $text = $node->text();

                    return trim($text);
                });
        } elseif ($productPageCrawler->filter('.b-link')->count()) {
            $attributes = $productPageCrawler->filter('.b-techspec__name')
                ->each(function ($node) {
                    $text = $node->text();
                    $attribute = strpos($text, '?') ? substr($node->text(), 0, strpos($text, '?')) : $text;

                    return trim($attribute);
                });
            $attributesValues = $productPageCrawler->filter('.b-techspec__item')
                ->each(function ($node) {
                    $node->removeChild($node->getElementsByTagName('span')->item(0));

                    $text = $node->text();
                    $attribute = strpos($text, '?') ? substr($node->text(), 0, strpos($text, '?')) : $text;
                    return trim($attribute);
                });
        } elseif ($productPageCrawler->filter('.n-offer-card-info__warnings')->count()) {
            $attributes = $productPageCrawler->filter('.n-product-spec-list__item')
                ->each(function ($node) {
                    $text = $node->text();
                    $parametersAr = explode(':', $text);

                    return trim($parametersAr[0]);
                });
            $attributesValues = $productPageCrawler->filter('.n-product-spec-list__item')
                ->each(function ($node) {
                    $text = $node->text();
                    $parametersAr = explode(':', $text);

                    return trim($parametersAr[1]);
                });
        }

        if (count($attributes)) {
            if (count($attributes) !== count($attributesValues)) {
                throw new \Exception('count attributes and attributes names different!');
            }
            foreach ($attributes as $num => $attributeName) {
                $this->addAttributeToProduct($product, $attributeName, $attributesValues[$num]);
            }
        } else {
            $this->dump(" Does not get attributes from page $urlForRequest");

            return false;
        }

        return true;
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
            if ($this->getProxyIp()) {
                $this->dump(" go to yandex to set cookie for ip: {$this->getProxyIp()->getIp()}");
            }
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
