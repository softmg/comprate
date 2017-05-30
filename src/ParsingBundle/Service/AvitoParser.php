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
use function GuzzleHttp\Psr7\parse_query;
use ParsingBundle\Entity\ParsingProductInfo;
use ParsingBundle\Entity\ParsingSite;
use ProductBundle\Entity\Price;
use ParsingBundle\Repository\ParsingSiteRepository;
use ParsingBundle\RequestObjects\ParsingPaginator;
use ProductBundle\Entity\AvitoOffer;
use ProductBundle\Entity\Offer;
use ProductBundle\Entity\ProductType;
use ProductBundle\Repository\OfferRepo;
use ProductBundle\Repository\ProductTypeRepository;
use ProductBundle\RequestObjects\CreateOfferRequest;
use ProductBundle\RequestObjects\GetOneOfferRequest;
use ProductBundle\RequestObjects\GetOneProductRequest;
use ProductBundle\RequestObjects\UpdateOfferTypeAndSiteRequest;
use ProductBundle\Services\OfferHandler;
use ProductBundle\Services\ProductHandler;
use Symfony\Component\DomCrawler\Crawler;

class AvitoParser extends BaseParser
{
    const TYPES = [
        ProductType::CPU => 'protsessory'
    ];

    protected $notUseCacheForSearch = false;

    const SORT_BY_NEW = 4;

    const SORT_BY_OLD = 3;

    const SEARCH_URL = 'https://www.avito.ru/moskva/tovary_dlya_kompyutera/komplektuyuschie/%s?p=%s&s=%s';

    /**
     * @var OfferRepo
     */
    private $offerRepo;

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

    /**
     * @var ProductTypeRepository
     */
    private $productTypeRepo;

    /**
     * @var OfferHandler
     */
    private $offerHandler;

    public function __construct(
        EntityManager $em,
        ProxyList $proxyList,
        $cache_dir,
        $rucaptcha_token,
        $proxy_userpasswd,
        $phantomJsScriptPath,
        ProductHandler $productHandler,
        OfferRepo $offerRepo,
        RequestObjectHandler $requestObjectHandler,
        ParsingSiteRepository $siteRepo,
        AvitoDateParser $avitoDateParser,
        ProductTypeRepository $productTypeRepo,
        OfferHandler $offerHandler
    )
    {
        parent::__construct($em, $proxyList, $cache_dir, $rucaptcha_token, $proxy_userpasswd, $phantomJsScriptPath, $productHandler);

        $this->offerRepo = $offerRepo;
        $this->requestObjectHandler = $requestObjectHandler;
        $this->siteRepo = $siteRepo;
        $this->site = $siteRepo->findOneBy(['name' => ParsingSite::AVITO]);
        $this->avitoDateParser = $avitoDateParser;
        $this->productTypeRepo = $productTypeRepo;
        $this->offerHandler = $offerHandler;
    }

    /**
     * Run parsing
     */
    public function run()
    {

        /** @var ProductType[] $types */
//        $types = $this->productTypeRepo->findAll();
        $types = $this->productTypeRepo->findBy(['code' => ProductType::CPU]);


//        foreach ($types as $type) {
//            $this->parseOffersList($type);
//        }

        $this->parseOffers();
    }

    /**
     * @param ProductType $type
     *
     * @return bool|string
     */
    private function getProductTypeURLFragment(ProductType $type)
    {
        $code = $type->getCode();

        if (!isset(self::TYPES[$code])) {
            return false;
        }

        return self::TYPES[$code];
    }


    public function parseOffers()
    {
        $offersIterator = $this->offerRepo->unhandledOffersQB($this->site)->getQuery()->iterate();

        $index = 0;

        foreach ($offersIterator as $row) {
            ++$index;

            /** @var Offer $offer */
            $offer = $row[0];

            $this->parseOffer($offer);

            if ($index % 100 === 0) {
                $this->em->flush();
                $this->em->clear(ParsingProductInfo::class);
            }
        }

        return true;
    }

    public function parseOffer(Offer $offer)
    {
        $url = $this->site->getUrl() . $offer->getProductInfo()->getUrl();
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

        $avitoOffer = new AvitoOffer($createAvitoOfferRequest);

        $offer->setAvitoOffer($avitoOffer);

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
     * @param ProductType $type
     *
     * @return bool
     */
    public function parseOffersList(ProductType $type)
    {
        $fragment = $this->getProductTypeURLFragment($type);

        if (false === $fragment) {
            return false;
        }

        $paginator = $this->findLastHandledPage($type);

        ++$paginator->page;

        for (; $paginator->page <= $paginator->totalPages; ++$paginator->page) {

            $this->getProductsFromPage($type, $paginator);

            $this->handlePage($paginator, $type);
            gc_collect_cycles();
        }

        return true;
    }

    /**
     * Find last handled page. Complexity O(log n)
     *
     * @param ProductType $type
     *
     * @return mixed|ParsingPaginator
     */
    public function findLastHandledPage(ProductType $type)
    {

        $firstPage = new ParsingPaginator();

        $firstPage->page = 1;

        $this->getProductsFromPage($type, $firstPage);

        if (!$this->isPageHandled($firstPage)) {
            $this->handlePage($firstPage, $type);

            return $firstPage;
        }

        return $this->traversePages($type, 2, $firstPage->totalPages, $firstPage->totalPages - 1);
    }

    public function handlePage(ParsingPaginator $paginator, ProductType $type)
    {
        $this->createOffers($paginator->items, $type);

        if (!$this->em->isOpen()) {
            return false;
        }

        $this->em->flush();

        $isHandled = $this->isPageHandled($paginator);

        $this->em->clear(Offer::class);

        return $isHandled;
    }

    private function traversePages(ProductType $type, $first, $last, $total)
    {
        if ($total <= 1) {
            $pageValue = new ParsingPaginator();
            $pageValue->page = $first;

            $this->getProductsFromPage($type, $pageValue, self::SORT_BY_OLD);

            if (!$this->isPageHandled($pageValue)) {
                $this->handlePage($pageValue, $type);
            }

            return $pageValue;
        }

        $page = $first + round($total / 2);

        $pageValue = new ParsingPaginator();
        $pageValue->page = $page;

        $this->getProductsFromPage($type, $pageValue, self::SORT_BY_OLD);

        if ($this->isPageHandled($pageValue)) {

            if ($page === $last) {
                return $pageValue;
            }

            return $this->traversePages($type, $page, $last, $last - $page);
        }

        $last = $page - 1;

        return $this->traversePages($type, $first, $last, $last - $first);
    }

    private function isPageHandled(ParsingPaginator $paginator)
    {

        $ids = $this->getIdsOnSiteFromPaginator($paginator);

        $count = $this->offerRepo->countByIdsOnSite($ids);

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
     * @param ProductType $type
     *
     * @return array
     */
    public function createOffers($productsRequest, ProductType $type)
    {
        $offers = [];

        foreach ($productsRequest as $request) {
            $getOneOfferRequest = new GetOneOfferRequest();
            $getOneOfferRequest->idOnSite = $request->idOnSite;

            $offer = $this->offerHandler->getOne($getOneOfferRequest, ['id-on-site-is-required']);

            if (!$offer) {
                $createOfferRequest = new CreateOfferRequest();
                $createOfferRequest->productInfo = new ParsingProductInfo($request);
                $createOfferRequest->type = $type;
                $createOfferRequest->site = $this->getParserSite();

                $offer = $this->offerHandler->createNew($createOfferRequest);
            }

            $updateOfferRequest = new UpdateOfferTypeAndSiteRequest();

            $updateOfferRequest->type = $type;
            $updateOfferRequest->site = $this->site;

            $offer->updateTypeAndSite($updateOfferRequest);

            $productInfo = $offer->getProductInfo();

            if (!$productInfo) {
                $productInfo = new ParsingProductInfo($request);
            }

            $productInfo->updateBaseInfo($request);

            $offers[] = $offer;
        }

        return $offers;
    }

    /**
     * @param ProductType $type
     * @param ParsingPaginator $paginator
     * @param int $sortBy
     *
     * @return ParsingPaginator
     */
    public function getProductsFromPage(ProductType $type, $paginator, $sortBy = self::SORT_BY_OLD)
    {
        $fragment = $this->getProductTypeURLFragment($type);

        $this->requestObjectHandler->validate($paginator, true);

        $urlForRequest = sprintf(self::SEARCH_URL, $fragment, $paginator->page, $sortBy);
        $crawlerPage = $this->getCrawlerPage($urlForRequest);

        $avitoDateParser = $this->avitoDateParser;

        $products = $crawlerPage->filter('.item')->each(function (Crawler $node) use ($avitoDateParser) {
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
