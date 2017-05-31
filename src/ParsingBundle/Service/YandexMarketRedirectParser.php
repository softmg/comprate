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

class YandexMarketRedirectParser extends YandexMarketParser
{
    /**
     * Run parsing
     */
    public function run()
    {
        $products = $this->getProductsForFirstParsing();

        /** disable proxy for redirects */
        $this->setNotUseProxy(true);
        $this->setNotTryAgainAfterFail(true);

        foreach ($products as $product) {
            $url = $product->getSite()->getUrl() . $product->getProductInfo()->getUrl();

            $this->getProductAttributesFromRedirect($product, $url);
        }
    }

    /**
     * Get product page after redirects
     * @param Product $product
     * @param String $redirectUrl
     * @return Crawler|false
     */
    protected function getProductPageFromRedirectUrl($product, $redirectUrl)
    {
        $pageCrawler = $this->getCrawlerPage($this->checkUrlScheme($redirectUrl), false, true);
        $responseArrray = $this->getCurrentClient()->getJsonResponseArray();
        if (!$pageCrawler || !isset($responseArrray['currentUrl'])) {
            return false;
        }
        $redirectedUrl = $responseArrray['currentUrl'];

        /* alex: add redirect url warning page parsing */
        if (strpos($redirectedUrl, 'www.yandex.ru/redir_warning') !== false) {
            $redirectedUrl = $pageCrawler->filter('.b-redir-warning__link')->getNode(0)->textContent;
            if (!$this->isExternalUrl($redirectedUrl)) {
                $pageCrawler = $this->getCrawlerPage($this->checkUrlScheme($redirectedUrl), false, true);
            }
        }

        $this->saveProduct($product, $redirectedUrl, true);

        if ($redirectedUrl &&
            $this->isExternalUrl($redirectedUrl)
        ) {
            return false;
        }

        return $pageCrawler;
    }

    /**
     * @param Product $product
     * @param String $redirectUrl
     * @throws \Exception
     */
    public function getProductAttributesFromRedirect($product, $redirectUrl)
    {
        $crawlerPage = $this->getProductPageFromRedirectUrl($product, $redirectUrl);

        if ($crawlerPage) {
            $charactersiticLink = $crawlerPage->selectLink('Характеристики');
            /* check if simple product page */
            if ($charactersiticLink->count()) {
                $urlForRequest = $charactersiticLink->getNode(0)
                    ->getAttribute('href');
                $urlForRequest = $this->clearUrl($urlForRequest);
                $crawlerPage = $this->getCrawlerPage($urlForRequest);
            }

            $result = $this->parseProductCharacteristicPage($product, $crawlerPage, $redirectUrl);

            /* if get parameters => save product info as success */
            if ($result) {
                $this->saveProduct($product, '', false);
            }
        }
    }

    /**
     * Get products notparsing yet on new site
     *
     * @return Product[]
     */
    protected function getProductsForFirstParsing()
    {
        //$productRepo = $this->em->getRepository('ProductBundle:Product');
        //$product = $productRepo->find(1166);

        $qb = $this->em->createQueryBuilder();
        $products = $qb->select('pr_in')
            ->from('ProductBundle:Offer', 'pr_in')
            ->leftJoin('ProductBundle:Product', 'p', Expr\Join::WITH, 'pr_in.product=p')
            ->where('pr_in.productInfo.url LIKE :redirUrl')
            //->andWhere('pr_in.product = :product')
            ->setParameter(':redirUrl', '%redir%')
            //->setParameter(':product', $product)
            ->orderBy('pr_in.productInfo.updatedAt', 'DESC')
            ->getQuery()
            ->execute()
        ;

        $this->dump(' get ' . count($products) . ' products for parsing');

        return $products;
    }
}
