<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

use Doctrine\ORM\EntityManager;
use Goutte\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use ParsingBundle\Entity\ParsingAttributeInfo;
use ParsingBundle\Entity\ParsingProductInfo;
use ParsingBundle\Entity\ParsingSite;
use ParsingBundle\Entity\ProxyIp;
use ParsingBundle\Repository\ParsingProductInfoRepository;
use ParsingBundle\Repository\ParsingSiteRepository;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\ProductAttribute;
use Symfony\Component\DomCrawler\Crawler;
use \ForceUTF8\Encoding;
use Doctrine\ORM\Query\Expr;

abstract class BaseParser
{
    const PRODUCT_ACTUALITY = 10;
    const SLEEP_BEFORE_SECOND_REQUEST = [3, 5];
    protected static $attributeValueReplacements = [
        'есть' => true,
        'нет' => false
    ];

    /** @var  EntityManager */
    private $em;

    /** @var  ProxyList */
    private $proxyList;

    /** @var  String */
    private $cache_dir;

    /** @var  String */
    private $rucaptcha_token;

    /** @var  Client */
    private $goutteClient;

    /** @var  Crawler */
    private $crawler;

    /** @var  array */
    private $clientParameters = [];

    /** @var  String (user:passwd) */
    private $proxy_userpasswd;

    /** @var  ProxyIp */
    private $proxyIp;

    /** @var bool */
    private $debug = false;

    /** @var bool */
    private $fromCache = false;

    // Will hold path to a writable location/file
    protected $cookieFilePath;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param ProxyList $proxyList
     * @param ProxyList $cache_dir
     * @param $rucaptcha_token
     */
    public function __construct(EntityManager $em, ProxyList $proxyList, $cache_dir, $rucaptcha_token)
    {
        $this->em = $em;
        $this->proxyList = $proxyList;
        $this->cache_dir = $cache_dir;
        $this->rucaptcha_token = $rucaptcha_token;

        /* if cli command then debug = true*/
        $this->debug = php_sapi_name() == 'cli';
    }

    /**
     * Realization for parsing function
     */
    abstract public function run();

    /**
     * @return String
     */
    abstract public function getParserSiteCode();

    /**
     * @param Crawler
     * @return Crawler
     */
    abstract public function recognizeAndEnterCaptcha($crawler);

    /**
     * @return ParsingSite
     */
    public function getParserSite()
    {
        return $this->getParsingSiteRepo()->findOneBy(['code' => ParsingSite::YANDEX_MARKET]);
    }

    /**
     * @throws \Exception
     * @return ParsingSite
     */
    public function getParserSiteAndCheck()
    {
        $site = $this->getParserSite();

        if (!$site) {
            throw new \Exception("Not found site with code \"{$this->getParserSiteCode()}\" in db");
        }

        return $site;
    }

    /**
     * @return ParsingSiteRepository
     */
    public function getParsingSiteRepo()
    {
        return $this->em->getRepository('ParsingBundle:ParsingSite');
    }

    /**
     * @return ParsingProductInfoRepository
     */
    public function getParsingProductInfoRepo()
    {
        return $this->em->getRepository('ParsingBundle:ParsingProductInfo');
    }

    /**
     * @param  array $params
     * @return array
     */
    public function setClientParameters($params)
    {
        return $this->clientParameters = $params;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        $goutteClient = new Client();
        $userAgent = false;

        /* settings to save cookie */
        $this->clientParameters['curl'][CURLOPT_COOKIEFILE] = $this->cache_dir . 'cookie.data';
        $this->clientParameters['curl'][CURLOPT_COOKIEFILE] = $this->cache_dir . 'cookie.data';
        $this->clientParameters['curl'][CURLOPT_RETURNTRANSFER] = 1;
        $cookieJar = new FileCookieJar($this->cache_dir . 'cookie.data', true);
        $this->clientParameters['cookies'] = $cookieJar;

        /* check use proxy ip */
        if ($this->getParserSite()->isUseProxy()) { // && !isset($this->clientParameters['proxy'])
            $proxyIp = $this->proxyList->getWhiteIp(true);
            $this->proxyIp = $proxyIp;
            $this->clientParameters['proxy'] = $proxyIp->getIp();

            $this->dump(" proxy ip: {$proxyIp->getIp()}, user-agent: {$proxyIp->getUserAgent()}");

            /* check if port for auth */
            if (strpos($proxyIp->getIp(), '8080') && $this->proxy_userpasswd) {
                $this->clientParameters['curl'][CURLOPT_PROXYUSERPWD] = $this->proxy_userpasswd;
            }

            $userAgent = $proxyIp->getUserAgent();
        }

        $guzzleClient = new \GuzzleHttp\Client($this->clientParameters);
        $goutteClient->setClient($guzzleClient);
        $goutteClient->setGuzzleCookieJar($cookieJar);
        $goutteClient->setHeader('User-Agent', $userAgent);

        $this->goutteClient = $goutteClient;

        return $goutteClient;
    }

    /**
     * @param String $pageUrl
     * @param Bool $forceNew
     * @param Bool $notUseCache
     * @throws \Exception
     * @return Crawler
     */
    public function getCrawlerPage($pageUrl, $forceNew = false, $notUseCache = false)
    {
        $this->fromCache = false;
        if ($notUseCache || ! $crawler = $this->getCacheCrawler($pageUrl)) {
            $this->fromCache = true;

            /* if second request after first success => use old client and sleep before next */
            if (!$forceNew && $client = $this->getGoutteClient()) {
                $this->sleepBeforeRequest();
            } else {
                $client = $this->getClient();
            }

            $crawler = $client->request('GET', $pageUrl);
            /* check, enter captcha if exist and return new crawler or null if fail enter captcha */
            $crawler = $this->checkAndEnterCaptcha($crawler);
            $response = $client->getResponse();

            /* if success response => save content to cache */
            if ($crawler && $this->checkSuccessResponse($response)) {
                $this->dump(" page $pageUrl success! Save it to cache");
                $this->proxyList->addProxyIpSuccess($this->proxyIp);
                $this->saveCacheContent($pageUrl, $response->getContent());
            } else {
                /*if fail try again*/
                $this->dump(" page $pageUrl fail! Try it again");

                if ($this->proxyIp) {
                    $this->proxyList->addProxyIpFail($this->proxyIp);
                }

                $crawler = $this->getCrawlerPage($pageUrl, true);
            }
        }

        if (!$crawler) {
            throw new \Exception("Error to get crawler page from url \"{$pageUrl}\"");
        }

        $this->crawler = $crawler;

        return  $crawler;
    }

    /**
     * sleep before next request
     */
    protected function sleepBeforeRequest()
    {
        $sleepSeconds = rand(self::SLEEP_BEFORE_SECOND_REQUEST[0], self::SLEEP_BEFORE_SECOND_REQUEST[1]);
        $this->dump(" wait {$sleepSeconds}s before next request", '');
        while ($sleepSeconds > 0) {
            sleep(1);
            $this->dump('.', '');
            $sleepSeconds--;
        }
        $this->dump('');
    }

    /**
     * @param Crawler $crawler
     * @return Crawler
     */
    private function checkAndEnterCaptcha($crawler)
    {
        $crawler = $this->recognizeAndEnterCaptcha($crawler);

        return $crawler;
    }

    /**
     * @param String $message
     * @param String $newLine
     */
    protected function dump($message, $newLine = "\r\n")
    {
        if ($this->debug) {
            echo "{$message}{$newLine}";
        }
    }

    /**
     * @param String
     * @return Crawler $crawler
     */
    protected function getCacheCrawler($pageUrl)
    {
        $crawler = null;

        $this->checkCacheDir();

        if (file_exists($this->getCacheFileName($pageUrl))) {
            $file = file_get_contents($this->getCacheFileName($pageUrl));

            /* make sure we have utf-8 encoding file */
            $file = Encoding::toUTF8($file);
            if ($file) {
                $crawler = new Crawler();
                $crawler->addHtmlContent($file, 'UTF-8');
                $this->dump(" get page $pageUrl from cache {$this->getCacheFileName($pageUrl)}");
            }
        }

        return $crawler;
    }

    /**
     * @param String $pageUrl
     * @param String $content
     * @return Bool
     */
    public function saveCacheContent($pageUrl, $content)
    {
        return @file_put_contents($this->getCacheFileName($pageUrl), $content);
    }

    /**
     * @param String $pageUrl
     * @return String
     */
    private function getCacheFileName($pageUrl)
    {
        return $this->cache_dir . md5($pageUrl);
    }

    /**
     * Check and create cache dir if not exist
     */
    private function checkCacheDir()
    {
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0777);
        }
    }

    /**
     * @return Client
     */
    protected function getGoutteClient()
    {
        return $this->goutteClient;
    }

    /**
     * @return Crawler
     */
    protected function getCrawler()
    {
        return $this->crawler;
    }

    /**
     * @return String
     */
    protected function getRucaptchaToken()
    {
        return $this->rucaptcha_token;
    }

    /**
     * @param object|null
     * @return Bool
     */
    protected function checkSuccessResponse($response)
    {
        return $response ? $response->getStatus() == 200 : false;
    }

    /**
     * @param String $proxy_userpasswd
     */
    public function setProxyUserpasswd($proxy_userpasswd)
    {
        $this->proxy_userpasswd = $proxy_userpasswd;
    }

    /**
     * Save product info into db
     * @param Product $product
     * @param String $productUrl
     */
    protected function saveProductInfo($product, $productUrl)
    {
        $productInfoRepo = $this->em->getRepository('ParsingBundle:ParsingProductInfo');
        $productInfo = $productInfoRepo->findOneBy(['product' => $product, 'site' => $this->getParserSite()]);

        if (!$productInfo) {
            $productInfo = new ParsingProductInfo();
            $productInfo->setUrl($productUrl);
            $productInfo->setProduct($product);
            $productInfo->setSite($this->getParserSite());
        }

        $this->em->persist($productInfo);
        $this->em->flush();

        $this->dump(" save product info for id: {$product->getId()}");
    }

    /**
     * Get products notparsing yet on new site
     */
    protected function getProductsForFirstParsing()
    {
        $checkTime = new \DateTime('now');
        $checkTime->modify('-' . self::PRODUCT_ACTUALITY . ' days');

        $qb = $this->em->createQueryBuilder();
        $products = $qb->select('p')
            ->from('ProductBundle:Product', 'p')
            ->leftJoin('ParsingBundle:ParsingProductInfo', 'pr_in', Expr\Join::WITH, 'pr_in.product=p')
            ->where('pr_in.updatedAt is NULL OR pr_in.updatedAt < :checkTime')
            ->setParameter(':checkTime', $checkTime)
            ->getQuery()
            ->execute()
            ;

        $this->dump(' get ' . count($products) . ' products for parsing');

        return $products;
    }

    /**
     * @param String $name
     * @return ParsingAttributeInfo
     */
    protected function addAttributeInfo($name)
    {
        $attributeInfoRepo = $this->em->getRepository('ParsingBundle:ParsingAttributeInfo');

        $attributeInfo = $attributeInfoRepo->findOneBy([
            'name' => $name,
            'site' => $this->getParserSite()
        ]);

        if (!$attributeInfo) {
            $attributeInfo = new ParsingAttributeInfo();

            $attributeInfo->setName($name);
            $attributeInfo->setSite($this->getParserSite());

            $this->em->persist($attributeInfo);
            $this->em->flush();

            $this->dump(" add new attribute info name '$name'");
        }

        return $attributeInfo;
    }

    /**
     * @param Product $product
     * @param String $name
     * @param String $value
     * @param Boolean $forceUpdate
     * @return Boolean
     */
    protected function addAttributeToProduct($product, $name, $value, $forceUpdate = false)
    {
        $result = true;
        $attributeInfo = $this->addAttributeInfo($name);

        if ($attribute = $attributeInfo->getAttribute()) {
            $productAttributeRepo = $this->em->getRepository('ProductBundle:ProductAttribute');
            $productAttribute = $productAttributeRepo->findOneBy([
                'product' => $product,
                'attribute' => $attribute
            ]);

            if (!$productAttribute || $forceUpdate) {
                if (!$productAttribute) {
                    $productAttribute = new ProductAttribute();
                    $productAttribute->setProduct($product);
                    $productAttribute->setAttribute($attribute);
                }

                $productAttribute->setValue($value);
                /* format product attribute value */
                $productAttribute = $this->formatProductAttributeValue($productAttribute);
                if ($forceUpdate) {
                    $this->dump(" update product (id:{$product->getId()}) attribute '$name' value: '$value'");
                } else {
                    $this->dump(" add product (id:{$product->getId()}) attribute '$name' value: '$value'");
                }

                $this->em->persist($productAttribute);
                $this->em->flush();
            }
        } else {
            $this->dump(" we need to set attribute to attribute info '$name'");
            $result = false;
        }

        return $result;
    }

    /**
     * Format parsed attribute value
     * @param ProductAttribute $productAttribute
     * @return ProductAttribute
     */
    protected function formatProductAttributeValue($productAttribute)
    {
        $value = $productAttribute->getValue();

        if (in_array($value, array_keys(self::$attributeValueReplacements))) {
            $value = self::$attributeValueReplacements[$value];
        }

        $attribute = $productAttribute->getAttribute();
        if ($attribute->getUnit()) {
            $value = trim(str_replace($attribute->getUnit(), '', $value));
        }

        $this->checkAttributeValueFromExists($attribute, $value);

        $productAttribute->setValue($value);

        return $productAttribute;
    }

    /**
     * Check if new vaule does not exist in vocabular for this attribute
     * @param Attribute $attribute
     * @param String $value
     * @throws \Exception
     */
    protected function checkAttributeValueFromExists($attribute, $value)
    {
        $attibuteValues = $attribute->getValues();
        if ($attibuteValues->count()) {
            $attributeValueRepo = $this->em->getRepository('ProductBundle:AttributeValue');
            $attributeValue = $attributeValueRepo->findOneBy([
                'attribute' => $attribute,
                'value' => $value
            ]);

            if (!$attributeValue) {
                throw new \Exception("Not found attribute value for \"{$attribute->getName()}\" value: \"$value\"");
            }
        }
    }

    protected function isFromCache()
    {
        return $this->fromCache;
    }
}
