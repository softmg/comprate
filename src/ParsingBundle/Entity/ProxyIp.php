<?php

namespace ParsingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;

/**
 * ProxyIp
 *
 * @ORM\Table(name="proxy_ip")
 * @ORM\Entity(repositoryClass="ParsingBundle\Repository\ProxyIpRepository")
 */
class ProxyIp
{
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
     * @ORM\Column(name="ip", type="string", length=20)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="user_agent", type="string", length=200)
     */
    private $userAgent;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $isActive = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_used", type="datetime", nullable=true)
     */
    private $lastUsed;

    /**
     * @var Integer
     *
     * @ORM\Column(name="num_fail", type="integer", nullable=true)
     */
    private $numFail;

    /**
     * @var Integer
     *
     * @ORM\Column(name="num_success", type="integer", nullable=true)
     */
    private $numSuccess;

    /**
     * @var Integer
     *
     * @ORM\Column(name="num_captcha", type="integer", nullable=true)
     */
    private $numCaptcha;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="next_use", type="datetime", nullable=true)
     */
    private $nextUse;

    /**
     * @var bool
     *
     * @ORM\Column(name="check_auth", type="boolean", nullable=true)
     */
    private $checkAuth = true;

    /**
     * @var string
     *
     * @ORM\Column(name="proxy_type", type="string", length=10)
     */
    private $proxyType;

    /**
     * @var ParsingSite
     *
     * @ORM\ManyToOne(targetEntity="ParsingSite")
     * @ORM\JoinColumn(name="parsing_site_id", referencedColumnName="id")
     */
    private $parsingSite;

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
     * Set ip
     *
     * @param string $ip
     *
     * @return ProxyIp
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set userAgent
     *
     * @param string $userAgent
     *
     * @return ProxyIp
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get userAgent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return ProxyIp
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set lastUsed
     *
     * @param \DateTime $lastUsed
     *
     * @return ProxyIp
     */
    public function setLastUsed($lastUsed)
    {
        $this->lastUsed = $lastUsed;

        return $this;
    }

    /**
     * Get lastUsed
     *
     * @return \DateTime
     */
    public function getLastUsed()
    {
        return $this->lastUsed;
    }

    /**
     * @return int
     */
    public function getNumFail()
    {
        return $this->numFail;
    }

    /**
     * @param int $numFail
     */
    public function setNumFail($numFail)
    {
        $this->numFail = $numFail;
    }

    /**
     * @return int
     */
    public function getNumSuccess()
    {
        return $this->numSuccess;
    }

    /**
     * @param int $numSuccess
     */
    public function setNumSuccess($numSuccess)
    {
        $this->numSuccess = $numSuccess;
    }

    /**
     * @return \DateTime
     */
    public function getNextUse()
    {
        return $this->nextUse;
    }

    /**
     * @param \DateTime $nextUse
     */
    public function setNextUse($nextUse)
    {
        $this->nextUse = $nextUse;
    }

    /**
     * @return int
     */
    public function getNumCaptcha()
    {
        return $this->numCaptcha;
    }

    /**
     * @param int $numCaptcha
     */
    public function setNumCaptcha($numCaptcha)
    {
        $this->numCaptcha = $numCaptcha;
    }

    /**
     * @return boolean
     */
    public function isCheckAuth()
    {
        return $this->checkAuth;
    }

    /**
     * @param boolean $checkAuth
     */
    public function setCheckAuth($checkAuth)
    {
        $this->checkAuth = $checkAuth;
    }

    /**
     * @return string
     */
    public function getProxyType()
    {
        return $this->proxyType;
    }

    /**
     * @param string $proxyType
     */
    public function setProxyType($proxyType)
    {
        $this->proxyType = $proxyType;
    }

    /**
     * @return \ParsingBundle\Entity\ParsingSite
     */
    public function getParsingSite()
    {
        return $this->parsingSite;
    }

    /**
     * @param \ParsingBundle\Entity\ParsingSite $parsingSite
     */
    public function setParsingSite($parsingSite)
    {
        $this->parsingSite = $parsingSite;
    }
}
