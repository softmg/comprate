<?php

namespace ParsingBundle\Entity;

use ApiBundle\RequestObject\ProductInfoRequest;
use Doctrine\ORM\Mapping as ORM;
use ProductBundle\Entity\ProductType;

/**
 * ParsingProductInfo
 *
 * @ORM\Embeddable()
 */
class ParsingProductInfo
{
    /**
     * @ORM\Column(name="id_on_site", type="string", unique=true, nullable=true, options={"default"=null})
     * @var string
     */
    private $idOnSite;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text")
     */
    private $url;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_fail", type="boolean")
     */
    private $isFail = false;

    /**
     * @var string
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * ParsingProductInfo constructor.
     *
     * @param ProductInfoRequest $request
     */
    public function __construct(ProductInfoRequest $request)
    {
        $this->updateBaseInfo($request);
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return ParsingProductInfo
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
     * @return boolean
     */
    public function getIsFail()
    {
        return $this->isFail;
    }

    /**
     * @param boolean $isFail
     */
    public function setIsFail($isFail)
    {
        $this->isFail = $isFail;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param string $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateModifiedDatetime()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    public function updateBaseInfo(ProductInfoRequest $request)
    {
        $this->url = $request->url;
        $this->idOnSite = $request->idOnSite;
        $this->isFail = $request->isFail;
        $this->createdAt = $request->createdAt;

        $this->updateModifiedDatetime();
    }
}
