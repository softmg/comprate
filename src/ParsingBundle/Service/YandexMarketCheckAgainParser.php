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
        $products = $this->getProductsForFirstParsing();

        foreach ($products as $product) {
            $this->getProductAttributes($product);
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
            $this->saveProduct($product, $productUrl, true);

            return;
        }

        $urlForRequest = $this->getCharacteristicUrl($productUrl, $productId);
        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        $this->parseProductCharacteristicPage($product, $crawlerPage, $urlForRequest);

        $this->saveProduct($product, $productUrl);
    }
    
    /**
     * Get products notparsing yet on new site
     *
     * @return Product[]
     */
    protected function getProductsForFirstParsing()
    {
        $qb = $this->em->createQueryBuilder();
        $products = $qb->select('pr_in')
            ->from('ProductBundle:Product', 'pr_in')
            ->where('pr_in.productInfo.isFail = :isFail')
            ->andWhere('pr_in.site = :site')
            ->setParameter(':isFail', true)
            ->setParameter(':site', $this->getParserSite())
            ->getQuery()
            ->execute()
        ;

        $this->dump(' get ' . count($products) . ' products for parsing');

        return $products;
    }
}
