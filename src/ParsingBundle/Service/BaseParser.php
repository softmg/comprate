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
use ParsingBundle\Entity\ParsingProductInfo;
use ParsingBundle\Entity\ParsingSite;
use ParsingBundle\Entity\ProxyIp;
use ParsingBundle\Repository\ParsingProductInfoRepository;
use ParsingBundle\Repository\ParsingSiteRepository;
use ProductBundle\Entity\Product;
use Symfony\Component\DomCrawler\Crawler;
use \ForceUTF8\Encoding;

abstract class BaseParser
{
    const PRODUCT_ACTUALITY = 10;

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

        /* check use proxy ip */
        if ($this->getParserSite()->isUseProxy()) { // && !isset($this->clientParameters['proxy'])
            $proxyIp = $this->proxyList->getWhiteIp(true);
            $this->proxyIp = $proxyIp;
            $this->clientParameters['proxy'] = $proxyIp->getIp();

            $this->dump(" proxy ip: {$proxyIp->getIp()}, user-agent: {$proxyIp->getUserAgent()}");

            /* check if port for auth */
            if (strpos($proxyIp->getIp(), '8080') && $this->proxy_userpasswd) {
                $clientParameters['curl'][CURLOPT_PROXYUSERPWD] = $this->proxy_userpasswd;
            }

            $userAgent = $proxyIp->getUserAgent();
        }

        $guzzleClient = new \GuzzleHttp\Client($this->clientParameters);
        $goutteClient->setClient($guzzleClient);
        $goutteClient->setHeader('User-Agent', $userAgent);

        $this->goutteClient = $goutteClient;

        return $goutteClient;
    }

    /**
     * @param String $pageUrl
     * @return Crawler
     */
    public function getCrawlerPage($pageUrl)
    {
        $client = $this->getClient();

        if (! $crawler = $this->getCacheCrawler($pageUrl)) {
            $crawler = $client->request('GET', $pageUrl);
            /* check, enter captcha if exist and return new crawler or null if fail enter captcha */
            $crawler = $this->checkAndEnterCaptcha($crawler);
            $response = $client->getResponse();
            
            /* if success response => save content to cache */
            if ($crawler && $this->checkSuccessResponse($response)) {
                $this->dump(" page $pageUrl success! Save it to cache");
                $this->saveCacheContent($pageUrl, $response->getContent());
            } else {
                /*if fail try again*/
                $this->dump(" page $pageUrl fail! Try it again");

                if ($this->proxyIp) {
                    $this->proxyList->addProxyIpFail($this->proxyIp);
                }

                $this->getCrawlerPage($pageUrl);
            }
        }

        $this->crawler = $crawler;

        return  $crawler;
    }

    /**
     * @param Crawler $crawler
     * @return Crawler
     */
    private function checkAndEnterCaptcha($crawler)
    {
        if ($this->getRucaptchaToken()) {
            $crawler = $this->recognizeAndEnterCaptcha($crawler);
        }

        return $crawler;
    }

    /**
     * @param String $message
     */
    protected function dump($message)
    {
        if ($this->debug) {
            echo "$message\r\n";
        }
    }

    /**
     * @param String
     * @return Crawler $crawler
     */
    private function getCacheCrawler($pageUrl)
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
                $this->dump(" get page $pageUrl from cache");
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
        //TODO: find to update
        $productInfo = new ParsingProductInfo();
        $productInfo->setUrl($productUrl);
        $productInfo->setProduct($product);
        $productInfo->setSite($this->getParserSite());

        $this->em->persist($productInfo);
        $this->em->flush();

        $this->dump(" save {$product->getId()} product info");
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
            ->leftJoin('ParsingBundle:ParsingProductInfo', 'pr_in')
            ->where('pr_in.updatedAt is NULL OR pr_in.updatedAt < :checkTime')
            ->setParameter(':checkTime', $checkTime)
            ->getQuery()
            ->execute();

        $this->dump(' get ' . count($products) . ' products for parsing');

        return $products;
    }
}
