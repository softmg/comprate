<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

use ParsingBundle\Entity\ParsingProductInfo;
use ProductBundle\Entity\Product;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\ORM\Query\Expr;

class YandexMarketCheckAgainParser extends YandexMarketParser
{
    /**
     * Run parsing
     */
    public function run()
    {
        $productsInfo = $this->getProductsForFirstParsing();

        /** @var ParsingProductInfo $productInfo */
        foreach ($productsInfo as $productInfo) {
            $this->getProductAttributes($productInfo->getProduct());
        }
    }
    
    /**
     * @param Product $product
     * @throws \Exception
     */
    public function getProductAttributes($product)
    {
        /* not use cache */
        $this->notUseCacheForSearch = true;

        $searchName = $product->getType()->getName() . ' ' . $product->getName();
        list($productUrl, $productId) = $this->getProductUrlAndId($searchName);

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
     * Get products notparsing yet on new site
     */
    protected function getProductsForFirstParsing()
    {
        $qb = $this->em->createQueryBuilder();
        $products = $qb->select('pr_in')
            ->from('ParsingBundle:ParsingProductInfo', 'pr_in')
            ->where('pr_in.isFail = :isFail')
            ->setParameter(':isFail', true)
            ->getQuery()
            ->execute()
        ;

        $this->dump(' get ' . count($products) . ' products for parsing');

        return $products;
    }
}
