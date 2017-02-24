<?php

namespace ParsingBundle\Service;

/**
 * Class PhantomJSWrapper
 * @package common\components\parsing
 */
class PhantomJSWrapper
{
    private $debug;
    private $pathToScript;
    private $script;
    private $proxy;
    private $proxyType = 'http';
    private $proxyAuth;
    private $cookiesFile;
    private $params = [];

    public function __construct($pathToScript)
    {
        $this->pathToScript = $pathToScript;
        $this->debug = true;
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

    public function execute($script, $params)
    {
        $cmd = 'phantomjs';
        $this->script = $this->pathToScript . "/$script";
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
        if ($result === null) {
            return false;
        }

        if ($result[0] !== '{') {
            return $result;
        }
        /*$json = json_decode($result, true);
        if ($json === null) {
            return false;
        }*/

        return $result;
    }
}
