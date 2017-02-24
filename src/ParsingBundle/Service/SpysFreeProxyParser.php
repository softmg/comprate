<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

class SpysFreeProxyParser extends BaseFreeProxyParser
{
    const PROXY_URL = 'http://spys.ru/free-proxy-list/RU/';

    /** @var  PhantomJSWrapper */
    private $phantomJsService;

    public function getParserSiteCode()
    {
        return 'spys';
    }
    
    /**
     * Set phantom js service
     * @param PhantomJSWrapper $phantomJsService
     */
    public function setPhantomJsService($phantomJsService)
    {
        $this->phantomJsService = $phantomJsService;
    }

    /**
     * Run parsing
     */
    public function run()
    {
        /* product list with new client */
        $crawlerPage = $this->getCrawlerPage(self::PROXY_URL, false, false, ['xpp' => 4]);
        $ips = $crawlerPage->filter('tr tr.spy1xx')->each(function ($node) {
            $ipInfo = [];
            $trIp = $node->getNode(0);
            $tdIp = $node->filter('td font.spy14')->getNode(0);
            if ($tdIp) {
                $encodedJsPort = substr(
                    preg_replace('/^[^\+]*?\+/s', '', $tdIp->childNodes->item(1)->textContent),
                    0,
                    -1
                );
                $port = $this->getPortFromJs($encodedJsPort);

                if ($port) {
                    $this->getProxyList()->addProxy(
                        $tdIp->childNodes->item(0)->wholeText . ":$port",
                        false,
                        $this->getProxyType($trIp->childNodes->item(1)->textContent)
                    );
                }
            }

            return $ipInfo;
        });

        $ips = $crawlerPage->filter('tr tr.spy1x')->each(function ($node) {
            $ipInfo = [];
            $trIp = $node->getNode(0);
            $tdIp = $node->filter('td font.spy14')->getNode(0);
            if ($tdIp) {
                $encodedJsPort = substr(
                    preg_replace('/^[^\+]*?\+/s', '', $tdIp->childNodes->item(1)->textContent),
                    0,
                    -1
                );
                $port = $this->getPortFromJs($encodedJsPort);

                if ($port) {
                    $this->getProxyList()->addProxy(
                        $tdIp->childNodes->item(0)->wholeText . ":$port",
                        false,
                        $this->getProxyType($trIp->childNodes->item(1)->textContent)
                    );
                }
            }

            return $ipInfo;
        });
    }

    /**
     * Get result from encoded js string like
     * "(Eight5TwoEight^Nine0One)+(Nine8ThreeZero^Two6Five)+(Four8SevenFour^EightNineTwo)+(EightEightOneOne^Eight9Zero)"
     * @param string $string
     * @return int
     */
    private function getPortFromJs($string)
    {
        $port = $this->phantomJsService->execute(
            'spysPort.js',
            ["$string"]
        );
        
        return (int)$port;
    }
}
