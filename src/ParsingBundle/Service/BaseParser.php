<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

use Doctrine\ORM\EntityManager;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Cookie\FileCookieJar;
use ParsingBundle\Entity\ParsingAttributeInfo;
use ParsingBundle\Entity\ParsingProductInfo;
use ParsingBundle\Entity\ParsingSite;
use ParsingBundle\Entity\ProxyIp;
use ParsingBundle\Repository\ParsingProductInfoRepository;
use ParsingBundle\Repository\ParsingSiteRepository;
use phpDocumentor\Reflection\Types\Boolean;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\ProductAttribute;
use ProductBundle\Entity\ProductType;
use Symfony\Component\DomCrawler\Crawler;
use \ForceUTF8\Encoding;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

abstract class BaseParser
{
    const PRODUCT_ACTUALITY = 100;
    protected static $sleepBeforeSecondRequest = [0, 1];
    protected static $attributeValueReplacements = [
        'есть' => true,
        'нет' => false,
        'да' => true,
        'Yes' => true,
        'No' => false,
        'N/A' => false
    ];

    protected static $attributeMeasReplacements = [
        'Гб' => ['gb'],
        'МГц' => ['MHz'],
        'В' => ['V']
    ];

    /** @var  EntityManager */
    protected $em;

    /** @var  ProxyList */
    protected $proxyList;

    /** @var  String */
    protected $phantomJsScriptPath;

    /** @var  String */
    protected $cache_dir;

    /** @var  String */
    private $rucaptcha_token;

    /** @var  GoutteClient|PhantomJSClient */
    private $currentClient;

    /** @var  Crawler */
    private $crawler;

    /** @var  array */
    protected $clientParameters = [];

    /** @var  String (user:passwd) */
    protected $proxy_userpasswd;

    /** @var  ProxyIp */
    protected $proxyIp;

    /** @var bool */
    private $debug = false;

    /** @var bool */
    private $fromCache = false;

    // Will hold path to a writable location/file
    protected $cookieFilePath;

    /** @var  String */
    private $parsedInnerHost;

    /** @var  Bool */
    private $notUseProxy = false;

    /** @var Bool */
    private $notTryAgainAfterFail = false;

    /** @var  String */
    private $response;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param ProxyList $proxyList
     * @param String $cache_dir
     * @param String $rucaptcha_token
     * @param String $proxy_userpasswd
     * @param String $phantomJsScriptPath
     */
    public function __construct(
        EntityManager $em,
        ProxyList $proxyList,
        $cache_dir,
        $rucaptcha_token,
        $proxy_userpasswd,
        $phantomJsScriptPath
    ) {
        $this->em = $em;
        $this->proxyList = $proxyList;
        $this->cache_dir = $cache_dir;
        $this->rucaptcha_token = $rucaptcha_token;
        $this->proxy_userpasswd = $proxy_userpasswd;

        /* if cli command then debug = true*/
        $this->debug = php_sapi_name() == 'cli';
        $this->cookieFilePath = $this->cache_dir . 'cookie.data';
        $this->phantomJsScriptPath = $phantomJsScriptPath;
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
        return $this->getParsingSiteRepo()->findOneBy(['code' => $this->getParserSiteCode()]);
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
     * @return GoutteClient
     */
    private function getGoutteClient()
    {
        $goutteClient = new GoutteClient();
        $userAgent = false;

        /* check use proxy ip */
        if ($this->getParserSite()->isUseProxy() && !$this->notUseProxy) { // && !isset($this->clientParameters['proxy'])
            $proxyIp = $this->proxyList->getWhiteIp(true);
            $this->proxyIp = $proxyIp;
            $this->clientParameters['proxy'] = $proxyIp->getIp();

            $this->dump(" proxy ip: {$proxyIp->getIp()}, user-agent: {$proxyIp->getUserAgent()}");

            /* check if port for auth */
            if (strpos($proxyIp->getIp(), '8080') && $this->proxy_userpasswd) {
                $this->clientParameters['curl'][CURLOPT_PROXYUSERPWD] = $this->proxy_userpasswd;
            }

            $this->cookieFilePath = $this->cache_dir . "cookie-{$proxyIp->getIp()}.data";

            $userAgent = $proxyIp->getUserAgent();
        }

        /* settings to save cookie */
        $this->clientParameters['curl'][CURLOPT_COOKIEFILE] = $this->cookieFilePath;
        $this->clientParameters['curl'][CURLOPT_COOKIEFILE] = $this->cookieFilePath;
        $this->clientParameters['curl'][CURLOPT_RETURNTRANSFER] = 1;
        $cookieJar = new FileCookieJar($this->cookieFilePath, true);
        $this->clientParameters['cookies'] = $cookieJar;

        $guzzleClient = new \GuzzleHttp\Client($this->clientParameters);
        $goutteClient->setClient($guzzleClient);
        $goutteClient->setGuzzleCookieJar($cookieJar);
        $goutteClient->setHeader('User-Agent', $userAgent);

        $this->currentClient = $goutteClient;

        return $goutteClient;
    }

    /**
     * @return PhantomJSClient
     */
    private function getPhantomJsClient()
    {
        $client = new PhantomJSClient($this->phantomJsScriptPath, $this->debug);

        /* check use proxy ip */
        if ($this->getParserSite()->isUseProxy() && !$this->notUseProxy) { // && !isset($this->clientParameters['proxy'])
            $proxyIp = $this->proxyList->getWhiteIp(true);
            $this->proxyIp = $proxyIp;
            $proxyType = $proxyIp->getProxyType() ? $proxyIp->getProxyType() : 'http';
            $client->setProxy($proxyIp->getIp());
            $client->setProxyType($proxyType);

            $this->dump(" proxy ip: {$proxyIp->getIp()}, user-agent: {$proxyIp->getUserAgent()}");

            /* check if port for auth */
            if (strpos($proxyIp->getIp(), '8080') && $this->proxy_userpasswd && $proxyIp->isCheckAuth()) {
                $client->setProxyAuth($this->proxy_userpasswd);
            }

            $client->setCookiesFile($this->cache_dir . "cookie-{$proxyIp->getIp()}.data");

            $userAgent = $proxyIp->getUserAgent();

            $client->setUserAgent($userAgent);
        } else {
            $client->setCookiesFile($this->cache_dir . "cookie.data");
        }

        $this->currentClient = $client;

        return $client;
    }

    /**
     * @return GoutteClient|PhantomJSClient
     */
    protected function getClient()
    {
        return $this->phantomJsScriptPath ? $this->getPhantomJsClient() : $this->getGoutteClient();
    }

    /**
     * @param String $pageUrl
     * @param Bool $forceNew
     * @param Bool $notUseCache
     * @param array $parameters
     * @throws \Exception
     * @return Crawler
     */
    public function getCrawlerPage($pageUrl, $forceNew = false, $notUseCache = false, $parameters = [])
    {
        $this->fromCache = false;
        if ($notUseCache || ! $crawler = $this->getCacheCrawler($pageUrl)) {
            /* if second request after first success => use old client and sleep before next */
            if (!$forceNew && $client = $this->getCurrentClient()) {
                if ($client->getResponse()) {
                    $this->sleepBeforeRequest();
                }
            } else {
                $client = $this->getClient();
            }

            if (count($parameters)) {
                $crawler = $client->request('POST', $pageUrl, $parameters);
            } else {
                $crawler = $client->request('GET', $pageUrl);
            }
            /* check, enter captcha if exist and return new crawler or null if fail enter captcha */
            if ($crawler) {
                $crawler = $this->checkAndEnterCaptcha($crawler);
            }
            $response = $client->getResponse();
            $this->response = $response->getContent();

            /* if success response => save content to cache */
            if ($crawler && $this->checkSuccessResponse($response) && $crawler->getUri() !== 'https://www.avito.ru/blocked') {
                $this->dump(" page $pageUrl success! Save it to cache {$this->getCacheFileName($pageUrl)}");
                if ($this->proxyIp) {
                    $this->proxyList->addProxyIpSuccess($this->proxyIp);
                }
                $this->saveCacheContent($pageUrl, $response->getContent());
            } elseif (!$this->notTryAgainAfterFail) {
                /*if fail try again*/
                $this->dump(" page $pageUrl fail! Try it again");

                if ($this->proxyIp) {
                    $this->proxyList->addProxyIpFail($this->proxyIp);
                }

                $crawler = $this->getCrawlerPage($pageUrl, true, $notUseCache);
            }
        }

        if (!$crawler && !$this->notTryAgainAfterFail) {
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
        $sleepUSeconds = rand(
            self::$sleepBeforeSecondRequest[0] * 1000000,
            self::$sleepBeforeSecondRequest[1] * 1000000
        );
        $sleepSeconds = floor($sleepUSeconds / 1000000);
        $this->dump(" wait {$sleepSeconds}s before next request", '');
        while ($sleepSeconds > 0) {
            sleep(1);
            $this->dump('.', '');
            $sleepSeconds--;
            $sleepUSeconds -= 1000000;
        }
        usleep($sleepUSeconds);
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
     * @return Boolean
     */
    protected function isFileExistInCache($pageUrl)
    {
        return file_exists($this->getCacheFileName($pageUrl));
    }

    /**
     * @param String
     * @return Crawler $crawler
     */
    protected function getCacheCrawler($pageUrl)
    {
        $crawler = null;

        $this->checkCacheDir();

        if ($this->isFileExistInCache($pageUrl)) {
            $file = file_get_contents($this->getCacheFileName($pageUrl));

            /* make sure we have utf-8 encoding file */
            $file = Encoding::toUTF8($file);
            $this->response = $file;
            if ($file) {
                $crawler = new Crawler();
                $crawler->addHtmlContent($file, 'UTF-8');
                $this->dump(" get page $pageUrl from cache {$this->getCacheFileName($pageUrl)}");
            }
        }

        $this->fromCache = true;

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
     * @param String $md5url
     * @return String
     */
    private function getCacheFileNameForMd5($md5url)
    {
        $firstSubDir = substr($md5url, 0, 2);
        $secondSubDir = substr($md5url, 2, 2);
        $this->checkCacheDir([$firstSubDir, $secondSubDir]);
        $cacheFolder = $this->cache_dir . "{$firstSubDir}/{$secondSubDir}/";

        return $cacheFolder . $md5url;
    }

    /**
     * @param String $pageUrl
     * @return String
     */
    private function getCacheFileName($pageUrl)
    {
        $md5url = md5($pageUrl);

        return $this->getCacheFileNameForMd5($md5url);
    }

    /**
     * Check and create cache dir if not exist
     * @param array $subdirs
     */
    private function checkCacheDir($subdirs = [])
    {
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0777);
        }

        $checkDir = $this->cache_dir;

        if ($subdirs && count($subdirs)) {
            foreach ($subdirs as $subdir) {
                $checkDir .= "/$subdir";
                if (!is_dir($checkDir)) {
                    mkdir($checkDir, 0777);
                }
            }
        }
    }

    /**
     * @return GoutteClient|PhantomJSClient
     */
    protected function getCurrentClient()
    {
        return $this->currentClient;
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
     * @param Boolean $isFail
     */
    protected function saveProductInfo($product, $productUrl, $isFail = false)
    {
        $productInfoRepo = $this->em->getRepository('ParsingBundle:ParsingProductInfo');
        $productInfo = $productInfoRepo->findOneBy(['product' => $product, 'site' => $this->getParserSite()]);

        if (!$productInfo) {
            $productInfo = new ParsingProductInfo();
            $productInfo->setProduct($product);
            $productInfo->setSite($this->getParserSite());
        }

        $changed = false;
        if ($productUrl) {
            $productUrl = $this->clearUrl($productUrl, false);
            if ($productInfo->getUrl() !== $productUrl) {
                $changed = true;
            }
            $productInfo->setUrl($productUrl);
        }
        $productInfo->setIsFail($isFail);
        if ($isFail !== $productInfo->getIsFail()) {
            $changed = true;
        }

        if ($changed) {
            $this->em->persist($productInfo);
            $this->em->flush();

            $this->dump(" save product info for id: {$product->getId()}");
        }
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
     * @param ProductType $productType
     * @return ParsingAttributeInfo
     */
    protected function addAttributeInfo($name, $productType)
    {
        $attributeInfoRepo = $this->em->getRepository('ParsingBundle:ParsingAttributeInfo');

        $attributeInfo = $attributeInfoRepo->findOneBy([
            'name' => $name,
            'site' => $this->getParserSite(),
            'productType' => $productType
        ]);

        if (!$attributeInfo) {
            $attributeInfo = new ParsingAttributeInfo();

            $attributeInfo->setName($name);
            $attributeInfo->setSite($this->getParserSite());
            $attributeInfo->setProductType($productType);

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
        $attributeInfo = $this->addAttributeInfo($name, $product->getType());

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

                $result = $productAttribute;
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

        /* delete: in the end of property*/
        $value = preg_replace('/:$/s', '', $value);

        if (in_array($value, array_keys(self::$attributeValueReplacements))) {
            $value = self::$attributeValueReplacements[$value];
        }

        $attribute = $productAttribute->getAttribute();
        if ($attribute->getUnit()) {
            $search []= $attribute->getUnit();
            if (isset(self::$attributeMeasReplacements[$attribute->getUnit()])) {
                $search = array_merge($search, self::$attributeMeasReplacements[$attribute->getUnit()]);
            }
            $value = trim(str_replace($search, '', $value));
        }

        if ($value === false) {
            $value = 0;
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

    /**
     * Check if we have cookie with name $name
     * @param String $name
     * @return Boolean
     */
    protected function hasCookie($name)
    {
        $cookies = $this->getCookies();

        if (count($cookies)) {
            foreach ($cookies as $cookie) {
                if ($cookie->Name === $name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get cookies for current ip
     */
    protected function getCookies()
    {
        $cookies = [];
        $cookiesString = @file_get_contents($this->cookieFilePath);

        if ($cookiesString) {
            $cookies = @json_decode($cookiesString);
        }

        return $cookies;
    }

    /**
     * @return ProxyIp
     */
    protected function getProxyIp()
    {
        return $this->proxyIp;
    }

    /**
     * @return proxyList
     */
    protected function getProxyList()
    {
        return $this->proxyList;
    }

    /**
     * Add captcha statistic to ip
     */
    protected function addProxyIpCaptcha()
    {
        $this->proxyList->addNumCaptcha($this->proxyIp);
    }

    protected function moveCacheToSubDirs()
    {
        $finder = new Finder();
        $finder->files()->depth('== 0')->in($this->cache_dir);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            // Dump the absolute path
            rename("{$file->getRealPath()}", "{$this->getCacheFileNameForMd5($file->getRelativePathname())}");
        }
    }

    /**
     * Clear url: check has scheme and not query from cache
     * @param String $url
     * @param Bool $clearQuery
     * @return String
     */
    protected function clearUrl($url, $clearQuery = true)
    {
        $url = $this->checkUrlScheme($url);
        if (strpos($url, 'http') === false) {
            $url = $this->getParserSite()->getUrl() . $url;
        }

        if ($this->isFromCache() && $clearQuery) {
            $url = $this->getUrlWithoutQuery($url);
        }

        return $url;
    }

    /**
     * Get url without query parameters
     * @param String $url
     * @return String
     */
    protected function getUrlWithoutQuery($url)
    {
        if ($urlParse = @parse_url($url)) {
            $url = "{$urlParse['scheme']}://{$urlParse['host']}{$urlParse['path']}";
        }

        return $url;
    }

    /**
     * Get parsing site scheme (http|https)
     * @return String
     */
    protected function getParsingSiteScheme()
    {
        $url = $this->getParserSite()->getUrl();
        $urlInfo = parse_url($url);

        return $urlInfo['scheme'];
    }

    /**
     * Check if url has scheme (http|https), if not than add
     * @param String $url
     * @return String
     */
    protected function checkUrlScheme($url)
    {
        if (strpos($url, '//') === 0) {
            $url = "{$this->getParsingSiteScheme()}:$url";
        }

        return $url;
    }

    /**
     * @param string $url
     * @return bool
     */
    protected function isExternalUrl($url)
    {
        $this->parsedInnerHost = $this->parsedInnerHost ?: parse_url($this->getParserSite()->getUrl())['host'];

        return parse_url($url)['host'] !== $this->parsedInnerHost;
    }

    protected function setNotUseProxy($notUseProxy)
    {
        $this->notUseProxy = $notUseProxy;
    }

    protected function setNotTryAgainAfterFail($notTryAgainAfterFail)
    {
        $this->notTryAgainAfterFail = $notTryAgainAfterFail;
    }

    protected function getResponse()
    {
        return $this->response;
    }

    /**
     * @param String
     * @param String
     * @param Bool
     * @return Product
     */
    protected function addProduct($name, $typeCode, $force = false)
    {
        $product = null;
        $productTypeRepo = $this->em->getRepository('ProductBundle:ProductType');

        if ($productType = $productTypeRepo->findOneBy(['code' => $typeCode])) {
            $productRepo = $this->em->getRepository('ProductBundle:Product');

            $product = $productRepo->findOneBy(['name' => $name, 'type' => $productType]);
            if ($product && $force) {
                $name = $name . '_';
                while ($product = $productRepo->findOneBy(['name' => $name, 'type' => $productType])) {
                    $name = $name . '_';
                }
            }
            if (!$product || $force) {
                $product = new Product();
                $product->setName($name);
                $product->setType($productType);

                $this->em->persist($product);
                $this->em->flush();

                $this->dump("add new $typeCode id:{$product->getId()}");
            }
        }

        return $product;
    }
}
