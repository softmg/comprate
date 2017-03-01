<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace common\components\parsing;

use common\models\ParsingSite;

class FoxtoolsFreeProxyParser extends BaseFreeProxyParser
{
    const PROXY_URL = 'http://foxtools.ru/Proxy';


    public function getParserSiteCode()
    {
        return ParsingSite::FOXTOOLS;
    }

    /**
     * Run parsing
     */
    public function run()
    {
        $crawlerPage = $this->getCrawlerPage(self::PROXY_URL, false, true);
        $this->addPageIps($crawlerPage);
        $lastPage = $crawlerPage->filter('.pager a')->last()->text();
        $lastPage = str_replace(['[', ']'], '', $lastPage);

        for ($i = 2; $i <= $lastPage; $i++) {
            $crawlerPage = $this->getCrawlerPage(self::PROXY_URL . '?page=' . $i, false, true);
            $this->addPageIps($crawlerPage);
        }
    }

    protected function addPageIps($crawlerPage)
    {
        $pIps = $crawlerPage->filter('table tr')->each(function ($node) {
            $ip = $node->getNode(0)->childNodes->item(2)->textContent;
            $port = $node->getNode(0)->childNodes->item(4)->textContent;
            $country = $node->getNode(0)->childNodes->item(6)->textContent;
            $anon = $node->getNode(0)->childNodes->item(8)->textContent;
            $type = $node->getNode(0)->childNodes->item(10)->textContent;
            $ip = preg_replace('/\s/s', '', $ip);
            $port = preg_replace('/\s/s', '', $port);
            $country = preg_replace('/ /s', '', $country);
            $anon = preg_replace('/\s/s', '', $anon);
            $type = preg_replace('/\s/s', '', $type);

            if (trim($country) === 'Россия(ru)' && trim($anon) !== 'низкая') {
                $this->getProxyList()->addProxy(
                    "$ip:$port",
                    false,
                    $this->getProxyType($type),
                    $this->getParserSite()->id
                );
            }
        });
    }
}
