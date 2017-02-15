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
use ParsingBundle\Entity\ParsingSite;
use ParsingBundle\Repository\ParsingProductInfoRepository;
use ParsingBundle\Repository\ParsingSiteRepository;
use Symfony\Component\DomCrawler\Crawler;

abstract class BaseParser
{
    /** @var  EntityManager */
    private $em;

    /** @var  ProxyList */
    private $proxyList;

    /** @var  array */
    private $clientParameters = [];

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param ProxyList $proxyList
     */
    public function __construct(EntityManager $em, ProxyList $proxyList)
    {
        $this->em = $em;
        $this->proxyList = $proxyList;
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
        /* check use proxy ip */
        if ($this->getParserSite()->isUseProxy() && !isset($this->clientParameters['proxy'])) {
            list($ip, $userAgent) = $this->proxyList->getWhiteIp(true, true);
            $this->clientParameters['proxy'] = $ip;
            $this->clientParameters['headers']['User-Agent'] = $userAgent;
        }
        $guzzleClient = new \GuzzleHttp\Client($this->clientParameters);
        $goutteClient->setClient($guzzleClient);

        return $goutteClient;
    }

    /**
     * @param String $pageUrl
     * @return Crawler
     */
    public function getCrawlerPage($pageUrl)
    {
        $client = $this->getClient();
        //TODO: cache results
        $crawler = $client->request('GET', $pageUrl);

        return  $crawler;
    }
}
