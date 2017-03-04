<?php

namespace ParsingBundle\Service;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\BrowserKit\Response;


/**
 * Class PhantomJSClient
 * @package common\components\parsing
 */
class PhantomJSClient
{
    /** @var  string Path to PhantomJS binary */
    private $pathToBin;
    private $debug;
    private $script;
    private $proxy;
    private $proxyType = 'http';
    private $proxyAuth;
    private $cookiesFile;
    private $params = [];
    private $response;
    private $jsonResponse;

    public function __construct($script, $debug = false)
    {
        $this->pathToBin = 'phantomjs';
        $this->script = $script;
        $this->debug = $debug;
    }

    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
        return $this;
    }

    public function setProxyType($proxyType)
    {
        $this->proxyType = $proxyType;
        return $this;
    }

    public function setProxyAuth($proxyUserPassword)
    {
        $this->proxyAuth = $proxyUserPassword;
        return $this;
    }

    public function setCookiesFile($filePath)
    {
        $this->cookiesFile = $filePath;
        return $this;
    }

    public function setUserAgent($userAgent)
    {
        $this->params['userAgent'] = $userAgent;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param $url
     * @param $referer
     * @param $postString
     * @return bool|string
     */
    public function execute($url, $referer, $postString = '')
    {
        $cmd = $this->pathToBin;
        if ($this->proxy) {
            $cmd .= " --proxy=\"{$this->proxy}\"";
        }
        if ($this->proxyType) {
            $cmd .= " --proxy-type={$this->proxyType}";
        }
        if ($this->proxyAuth) {
            $cmd .= " --proxy-auth=\"{$this->proxyAuth}\"";
        }
        if ($this->cookiesFile) {
            $this->params['cookies-file'] = $this->cookiesFile;
        }

        $this->params['postString'] = $postString;

        $args = array_map(function ($arg) {
            return '"' . $arg . '"';
        }, array_merge(['url' => $url, 'referer' => $referer], $this->params));
        array_unshift($args, $this->script);

        /* alex: not escape args because 404 error when escape ? like this https://market.yandex.ru/search.xml\?text=...*/

        $argString = implode(' ', $args);
        $cmd = escapeshellcmd($cmd). ' ' . $argString;
        if (!$this->debug) {
            $cmd .= ' 2>&1';
        } else {
            $this->dump($cmd);
        }

        $response = shell_exec($cmd);
        if ($response === null) {
            $this->jsonResponse = false;
        } elseif ($response[0] !== '{') {
            $this->jsonResponse = $response;
        } else {
            $json = json_decode($response, true);
            $this->jsonResponse = $json === null ? false : $json;
        }

        return $this->jsonResponse;
    }

    private function updateResponse()
    {
        $responseCode = isset($this->jsonResponse['pageContent']) &&
                        isset($this->jsonResponse['status']) &&
                        $this->jsonResponse['status'] === 'success' ?
                        200:
                        403;
        $this->response = new Response(
            isset($this->jsonResponse['pageContent']) ? $this->jsonResponse['pageContent'] : '',
            $responseCode
        );
    }

    public function cmd($params)
    {
        $cmd = $this->pathToBin;
        if ($this->proxy) {
            $cmd .= " --proxy=\"{$this->proxy}\"";
        }
        if ($this->proxyType) {
            $cmd .= " --proxy-type={$this->proxyType}";
        }
        if ($this->proxyAuth) {
            $cmd .= " --proxy-auth=\"{$this->proxyAuth}\"";
        }
        if ($this->cookiesFile) {
            $this->params['cookies-file'] = $this->cookiesFile;
        }

        $args = array_map(function ($arg) {
            return '"' . $arg . '"';
        }, array_merge($params));
        array_unshift($args, $this->script);

        $cmd = escapeshellcmd($cmd) . ' ' . implode(' ', $args);
        if (!$this->debug) {
            $cmd .= ' 2>&1';
        }

        $result = shell_exec($cmd);

        return $result;
    }

    /**
     * @param String $method
     * @param String $url
     * @param [] $parameters
     * @throws \Exception
     * @return Crawler
     */
    public function request($method, $url, $parameters = [])
    {
        $postString = '';

        if ($method === 'POST' && !$parameters) {
            throw new \Exception('not parameters for post phantom request');
        }

        if (is_array($parameters) && count($parameters)) {
            foreach ($parameters as $name => $value) {
                $postString .= "&$name=$value";
            }
            $postString = substr($postString, 1);
        }
        
        $response = $this->execute($url, 'no-referer', $postString);

        $this->updateResponse();

        if ($response &&
            isset($response['pageContent']) &&
            isset($response['status']) &&
            $response['status'] === 'success'
        ) {
            $crawler = new Crawler();
            $crawler->addHtmlContent($response['pageContent']);
        } else {
            $crawler = false;
        }

        return $crawler;
    }

    /**
     * @return array
     */
    public function getJsonResponseArray()
    {
        return $this->jsonResponse;
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
}
