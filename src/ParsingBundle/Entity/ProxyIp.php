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
     * @ORM\Column(name="user_agent", type="string", length=150)
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
}
