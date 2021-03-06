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
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Offer;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\ProductType;
use Symfony\Component\DomCrawler\Crawler;

class PcpartpickerProductInfoParser extends BaseParser
{
    protected $numPages = false;

    /**
     * @return String
     */
    public function getParserSiteCode()
    {
        return ParsingSite::PCPARTPICKER;
    }

    public function run()
    {
        $products = $this->getProductsForFirstParsing();

        foreach ($products as $product) {
            $url = $product->getSite()->getUrl() . $product->getProductInfo()->getUrl();

            $this->getProductAttributes($product, $url);
        }
    }

    protected function getProductAttributes($product, $urlForRequest)
    {
        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        $this->parseProductCharacteristicPage($product, $crawlerPage, $urlForRequest);
    }

    /**
     * Parse page with product characteristics
     * @param Product $product
     * @param Crawler $productPageCrawler
     * @param $urlForRequest
     * @return bool
     * @throws \Exception
     * @internal param $Crawler
     * @internal param $String
     */
    protected function parseProductCharacteristicPage($product, Crawler $productPageCrawler, $urlForRequest)
    {
        $attributes = [];
        $attributesValues = [];

        if ($productPageCrawler->filter('.specs h4')->count()) {
            $attributes = $productPageCrawler->filter('.specs h4')
                ->each(function (Crawler $node) {
                    $text = $node->text();

                    return trim($text);
                });
            $specHtml = $productPageCrawler->filter('.specs')->getNode(0)->textContent;

            $specAr = explode("\n", $specHtml);

            $specAr = array_filter($specAr, function ($element) use ($attributes) {
                $element = trim($element);

                return $element && $element !== 'Specifications' && !in_array($element, $attributes);
            });

            $specAr = array_values($specAr);
            $attributesValues = array_map('trim', $specAr);
        }

        if (count($attributes)) {
            if (count($attributes) !== count($attributesValues)) {
                throw new \Exception('count attributes and attributes names different!');
            }

            $memoryFreq = '';
            $memoryFormFactor = '';
            $memoryType = '';
            $result = false;
            foreach ($attributes as $num => $attributeName) {
                $attributeValue = $attributesValues[$num];
                if ($product->getType()->getCode() === ProductType::MOTHERBOARD &&
                    $attributeName == 'Memory Slots' &&
                    $explodeValues = explode('x', $attributeValue)
                ) {
                    $attributeValue = (int)$explodeValues[0];
                    $memoryFormFactor = trim($explodeValues[1]);
                }

                if ($product->getType()->getCode() === ProductType::MOTHERBOARD &&
                    $attributeName == 'Memory Type' &&
                    $explodeValues = explode('-', $attributeValue)
                ) {
                    $attributeValue = trim($explodeValues[0]);
                    $memoryFreq = trim($explodeValues[1]);
                }

                if ($product->getType()->getCode() === ProductType::MEMORY &&
                    $attributeName == 'Speed' &&
                    $explodeValues = explode('-', $attributeValue)
                ) {
                    $memoryType = trim($explodeValues[0]);
                    $attributeValue = trim($explodeValues[1]);
                }

                if ($this->addAttributeToProduct($product, $attributeName, $attributeValue)) {
                    $result = true;
                }
            }

            if ($productPageCrawler->filter('.merchant')->count()) {
                $product->setIsActual(true);
            } else {
                $product->setIsActual(false);
            }

            if ($memoryFreq) {
                $this->addAttributeToProduct($product, Attribute::MOTH_MEMORY_FREQ, $memoryFreq);
            }

            if ($memoryFormFactor) {
                $this->addAttributeToProduct($product, Attribute::MOTH_MEMORY_FORM_FACTOR, $memoryFormFactor);
            }

            if ($memoryType) {
                $this->addAttributeToProduct($product, Attribute::MOTH_MEMORY_TYPE, $memoryType);
            }

            $this->em->persist($product);
            $this->em->flush();

            if ($result) {
                $this->saveProduct($product, '');
            }
        } else {
            $this->dump(" Does not get attributes from page $urlForRequest");

            return false;
        }

        return true;
    }

    /**
     * @param Crawler $crawler
     * @return Crawler
     */
    public function recognizeAndEnterCaptcha($crawler)
    {
        $captchaCrawler = $crawler->filter('.g-recaptcha iframe');
        if ($captchaCrawler->count()) {
            $this->dump(" CAPTCHA! Try to recognize captcha");
            
            //$this->addProxyIpCaptcha();

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
     * @return Product[]
     */
    protected function getProductsForFirstParsing()
    {
        $qb = $this->em->createQueryBuilder();
        $products = $qb->select('pr_in')
            ->from('ProductBundle:Product', 'pr_in')
            //->leftJoin('ProductBundle:Product', 'p', Expr\Join::WITH, 'pr_in.product=p')
            ->where('pr_in.productInfo.isFail = :isFail')
            ->andWhere('pr_in.site = :site')
            ->setParameter(':isFail', true)
            ->setParameter(':site', $this->getParserSite())
            //->setFirstResult(1000)
            //->orderBy('pr_in.id', 'DESC')
            ->getQuery()
            ->execute()
        ;

        $this->dump(' get ' . count($products) . ' products for parsing');

        return $products;
    }
}
