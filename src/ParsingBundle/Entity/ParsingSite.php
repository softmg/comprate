<?php

namespace ParsingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParsingSite
 *
 * @ORM\Table(name="parsing_site")
 * @ORM\Entity(repositoryClass="ParsingBundle\Repository\ParsingSiteRepository")
 */
class ParsingSite
{
    const YANDEX_MARKET = 'yandex_market';
    const AVITO = 'avito';
    const PCPARTPICKER = 'pcpartpicker';
    const BENCHMARK = 'benchmark';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_proxy", type="boolean", nullable=true)
     */
    private $useProxy = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_free_proxy", type="boolean", nullable=true)
     */
    private $isFreeProxy = false;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ParsingSite
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return ParsingSite
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return boolean
     */
    public function isUseProxy()
    {
        return $this->useProxy;
    }

    /**
     * @param boolean $useProxy
     */
    public function setUseProxy($useProxy)
    {
        $this->useProxy = $useProxy;
    }

    /**
     * @return boolean
     */
    public function isIsFreeProxy()
    {
        return $this->isFreeProxy;
    }

    /**
     * @param boolean $isFreeProxy
     */
    public function setIsFreeProxy($isFreeProxy)
    {
        $this->isFreeProxy = $isFreeProxy;
    }
}
