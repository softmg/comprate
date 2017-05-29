<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */

namespace ParsingBundle\Service;

use ApiBundle\RequestObject\CreateAvitoOfferRequest;
use ApiBundle\RequestObject\ProductInfoRequest;
use ApiBundle\RequestObject\RequestObjectHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use function GuzzleHttp\Psr7\parse_query;
use ParsingBundle\Entity\ParsingProductInfo;
use ParsingBundle\Entity\ParsingSite;
use ParsingBundle\Entity\Price;
use ParsingBundle\Repository\ParsingProductInfoRepository;
use ParsingBundle\Repository\ParsingSiteRepository;
use ParsingBundle\RequestObjects\ParsingPaginator;
use ProductBundle\Entity\AvitoOffer;
use Symfony\Component\DomCrawler\Crawler;

class AvitoParser extends BaseParser
{
    protected $notUseCacheForSearch = false;

    const SORT_BY_NEW = 4;

    const SORT_BY_OLD = 3;

    const SEARCH_URL = 'https://www.avito.ru/moskva/tovary_dlya_kompyutera/komplektuyuschie/%s?p=%s&s=%s';

    /**
     * @var ParsingProductInfoRepository
     */
    private $productInfoRepo;

    /**
     * @var RequestObjectHandler
     */
    private $requestObjectHandler;

    /**
     * @var ParsingSiteRepository
     */
    private $siteRepo;

    /**
     * @var ParsingSite
     */
    private $site;

    /**
     * @var AvitoDateParser
     */
    private $avitoDateParser;

    public function __construct(
        EntityManager $em,
        ProxyList $proxyList,
        $cache_dir,
        $rucaptcha_token,
        $proxy_userpasswd,
        $phantomJsScriptPath,
        ParsingProductInfoRepository $productInfoRepo,
        RequestObjectHandler $requestObjectHandler,
        ParsingSiteRepository $siteRepo,
        AvitoDateParser $avitoDateParser
    )
    {
        parent::__construct($em, $proxyList, $cache_dir, $rucaptcha_token, $proxy_userpasswd, $phantomJsScriptPath);

        $this->productInfoRepo = $productInfoRepo;
        $this->requestObjectHandler = $requestObjectHandler;
        $this->siteRepo = $siteRepo;
        $this->site = $siteRepo->findOneBy(['name' => ParsingSite::AVITO]);
        $this->avitoDateParser = $avitoDateParser;
    }


    /**
     * Run parsing
     */
    public function run()
    {
//        $this->parseOffersList('protsessory');
        $this->parseOffers();
    }


    public function parseOffers()
    {
        $offersIterator = $this->productInfoRepo->unhandledProductsQB($this->site)->getQuery()->iterate();

        $index = 0;

        foreach ($offersIterator as $row) {
            ++$index;

            /** @var ParsingProductInfo $offer */
            $offer = $row[0];

            $this->parseOffer($offer);

            if ($index % 100 === 0) {
                $this->em->flush();
                $this->em->clear(ParsingProductInfo::class);
            }
        }

        return true;
    }

    public function parseOffer(ParsingProductInfo $offer)
    {
        $url = $this->site->getUrl() . $offer->getUrl();
        $page = $this->getCrawlerPage($url);

        $title = $page->filter('.title-info-title-text');
        $description = $page->filter('.item-description');
        $price = $page->filter('.price-value');
        $photos = $page->filter('.gallery-img-frame')->each(function (\Symfony\Component\DomCrawler\Crawler $node) {
            return $node->attr('data-url');
        });

        $sellerInfo = $page->filter('.seller-info-name');
        $phoneJS = $page->filter('.avito-ads-container + script');

        if (0 === $title->count()) {
            return false;
        }

        $createAvitoOfferRequest = new CreateAvitoOfferRequest();
        $createAvitoOfferRequest->name = $title->text();
        $createAvitoOfferRequest->description = $this->getNodeText($description);
        $createAvitoOfferRequest->price = $this->parsePrice($price);
        $createAvitoOfferRequest->photos = $photos;
        $createAvitoOfferRequest->username = $this->getNodeText($sellerInfo);
        $createAvitoOfferRequest->phone = $this->parsePhone($phoneJS, $url);

        $offer->createAvitoOffer($createAvitoOfferRequest);

        return true;
    }

    private function parsePhone(Crawler $phoneJS, $offerUrl)
    {
        $js = $this->getNodeText($phoneJS);

        if (!$js) {
            return null;
        }

        $token = $this->parseVarFromJS($js, 'avito.item.phone');
        $id = $this->parseVarFromJS($js, 'avito.item.id');

        if (!$token || !$id) {
            return null;
        }

        $privateKey = $this->phoneDemixer($id, $token);

        if (!$privateKey) {
            return null;
        }

        $url = "{$this->site->getUrl()}/items/phone/{$id}?pkey={$privateKey}&vsrc=r";

        $crawler = $this->getCrawlerPage($url, false, false, [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => $offerUrl,
        ]);

        $element = $crawler->filter('p');

        if ($element->count() === 0) {
            return null;
        }

        $parsed = json_decode($element->text(), true);

        if (json_last_error() !== JSON_ERROR_NONE || isset($parsed['error']) || !isset($parsed['image64'])) {
            return null;
        }

        return $parsed['image64'];
    }


    private function parseVarFromJS($js, $varName)
    {
        $varName = preg_quote($varName);

        if (0 === preg_match("/{$varName}\s=\s'([^']+)'/u", $js, $phoneMatches)) {
            return null;
        }

        return $phoneMatches[1];
    }

    private function phoneDemixer($id, $token)
    {
        if (!$token) {
            return '';
        }

        if (0 === preg_match_all('/[0-9a-f]+/u', $token, $matches)) {
            return '';
        }

        $token = join('', $id % 2 === 0 ? array_reverse($matches[0]): $matches[0]);

        $tokenLen = mb_strlen($token);

        $shortToken = '';

        for ($index = 0; $index < $tokenLen; ++$index) {
            if ($index % 3 !== 0) {
                continue;
            }

            $shortToken .= $token[$index];
        }

        return $shortToken;
    }

    private function parsePrice(Crawler $crawler = null)
    {
        $value = $this->getNodeText($crawler->filter('.price-value-string'));
        $currency = $this->getNodeText($crawler->filter('.font_arial-rub'));

        if ($value) {
            $value = (int)str_replace(' ', '', $value);
        }

        return new Price($value, null, $currency);
    }

    private function getNodeText(Crawler $node, $defaultValue = null, $trim = true)
    {
        if ($node->count() === 0) {
            return $defaultValue;
        }

        return $trim ? trim($node->text()) : $node->text();
    }

    /**
     * @param string $productType
     */
    public function parseOffersList($productType = 'protsessory')
    {
        $paginator = $this->findLastHandledPage($productType);

        ++$paginator->page;

        for (; $paginator->page <= $paginator->totalPages; ++$paginator->page) {

            $this->getProductsFromPage($productType, $paginator);

            $this->handlePage($paginator);
            gc_collect_cycles();
        }
    }

    /**
     * Find last handled page. Complexity O(log n)
     *
     * @param string $productType
     *
     * @return mixed|ParsingPaginator
     */
    public function findLastHandledPage($productType)
    {
        $firstPage = new ParsingPaginator();

        $firstPage->page = 1;

        $this->getProductsFromPage($productType, $firstPage);

        if (!$this->isPageHandled($firstPage)) {
            $this->handlePage($firstPage);

            return $firstPage;
        }

        return $this->traversePages($productType, 2, $firstPage->totalPages, $firstPage->totalPages - 1);
    }

    public function handlePage(ParsingPaginator $paginator)
    {
        $this->createProducts($paginator->items);

        if (!$this->em->isOpen()) {
            return;
        }

        $this->em->flush();

        $isHandled = $this->isPageHandled($paginator);

        $this->em->clear(ParsingProductInfo::class);

        return $isHandled;
    }

    private function traversePages($productType, $first, $last, $total)
    {
        if ($total <= 1) {
            $pageValue = new ParsingPaginator();
            $pageValue->page = $first;

            $this->getProductsFromPage($productType, $pageValue, self::SORT_BY_OLD);

            if (!$this->isPageHandled($pageValue)) {
                $this->handlePage($pageValue);
            }

            return $pageValue;
        }

        $page = $first + round($total / 2);

        $pageValue = new ParsingPaginator();
        $pageValue->page = $page;

        $this->getProductsFromPage($productType, $pageValue, self::SORT_BY_OLD);

        if ($this->isPageHandled($pageValue)) {

            if ($page === $last) {
                return $pageValue;
            }

            return $this->traversePages($productType, $page, $last, $last - $page);
        }

        $last = $page - 1;

        return $this->traversePages($productType, $first, $last, $last - $first);
    }

    private function isPageHandled(ParsingPaginator $paginator)
    {

        $ids = $this->getIdsOnSiteFromPaginator($paginator);

        $count = $this->productInfoRepo->countByIdsOnSite($ids);

        return count($ids) === $count;
    }

    public function getIdsOnSiteFromPaginator(ParsingPaginator $paginator)
    {
        return array_map(function (ProductInfoRequest $request) {
            return $request->idOnSite;
        }, $paginator->items);
    }


    /**
     * @param ProductInfoRequest[] $productsRequest
     *
     * @return array
     */
    public function createProducts($productsRequest)
    {
        $products = [];

        foreach ($productsRequest as $request) {
            $product = $this->productInfoRepo->findByIdOnSite($request->idOnSite);

            if ($product) {
                $errors = $this->requestObjectHandler->validate($request);

                if ($errors->count() > 0) {
                    continue;
                }

                $product->updateBaseInfo($request);
            } else {

//                $errors = $this->requestObjectHandler->validate($request, ['create']);
//
//                if ($errors->count() > 0) {
//                    continue;
//                }

                $product = new ParsingProductInfo($request);
                $this->productInfoRepo->add($product);
            }

            $products[] = $product;
        }

        return $products;
    }

    /**
     * @param string $productType
     * @param ParsingPaginator $paginator
     *
     * @param int $sortBy
     * @return ParsingPaginator
     */
    public function getProductsFromPage($productType, $paginator, $sortBy = self::SORT_BY_OLD)
    {
        $this->requestObjectHandler->validate($paginator, null, true);

        $urlForRequest = sprintf(self::SEARCH_URL, $productType, $paginator->page, $sortBy);
        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        $site = $this->site;
        $avitoDateParser = $this->avitoDateParser;

        $products = $crawlerPage->filter('.item')->each(function (Crawler $node) use ($site, $avitoDateParser) {
            $title = trim($node->filter('h3 a')->first()->text());
            $url = $node->filter('h3 a')->getNode(0)->getAttribute('href');
            $price = preg_replace('/^\s+([\d\s]*?)\s+руб.*?$/s', '$1', $node->filter('.about')->first()->text());
            $price = (int)str_replace(' ', '', $price);
            $id = $node->attr('id');
            $siteName = ParsingSite::AVITO;
            $idOnSite = "{$siteName}_{$id}";
            $dateNode = $node->filter('.date')->getNode(0);
            $createdAt = $avitoDateParser($dateNode ? $dateNode->textContent : null);

            $request = new ProductInfoRequest();
            $request->url = $url;
            $request->price = $price;
            $request->title = $title;
            $request->site = $site;
            $request->idOnSite = $idOnSite;
            $request->createdAt = $createdAt;

            return $request;
        });

        $paginator->pageSize = 50;
        $paginator->totalPages = $this->parseLastPageNumber($crawlerPage);

        $paginator->items = $products;

        return $paginator;
    }

    private function parseLastPageNumber(Crawler $crawler)
    {
        $lastPaginationElements = $crawler->filter('.pagination-page:contains("Последняя")');

        if ($lastPaginationElements->count() === 0) {
            return null;
        }

        $lastPaginationElement = $lastPaginationElements->first();
        $lastPaginationUri = $lastPaginationElement->attr('href');

        $query = parse_url($lastPaginationUri, PHP_URL_QUERY);

        $parsedQuery = parse_query($query);

        if (!array_key_exists('p', $parsedQuery)) {
            return null;
        }

        return (int)$parsedQuery['p'];
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
