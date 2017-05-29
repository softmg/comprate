<?php

namespace ParsingBundle\Entity;

use ApiBundle\RequestObject\ProductInfoRequest;
use Doctrine\ORM\Mapping as ORM;
use ProductBundle\Entity\AvitoOffer;
use ProductBundle\Entity\Product;

/**
 * ParsingProductInfo
 *
 * @ORM\Table(name="parsing_product_info")
 * @ORM\Entity(repositoryClass="ParsingBundle\Repository\ParsingProductInfoRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ParsingProductInfo
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
     * @ORM\Column(name="id_on_site", type="string", unique=true, nullable=true, options={"default"=null})
     * @var string
     */
    private $idOnSite;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Product", inversedBy="offers")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var ParsingSite
     *
     * @ORM\ManyToOne(targetEntity="ParsingSite")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     */
    private $site;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=3000)
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
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $offerCreatedAt;

    /**
     * @ORM\Embedded(class="ProductBundle\Entity\AvitoOffer")
     * @var AvitoOffer
     */
    private $avitoOffer;

    /**
     * ParsingProductInfo constructor.
     *
     * @param ProductInfoRequest $request
     */
    public function __construct(ProductInfoRequest $request)
    {
        $this->updateBaseInfo($request);
        $this->setCreatedAt(new \DateTime('now'));
    }

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
     * Set product
     *
     * @param Product $product
     *
     * @return ParsingProductInfo
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set site
     *
     * @param ParsingSite $site
     *
     * @return ParsingProductInfo
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return ParsingSite
     */
    public function getSite()
    {
        return $this->site;
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

    /**
     * @param AvitoOffer|null $offer
     *
     * @return self
     */
    public function setAvitoOffer(AvitoOffer $offer = null)
    {
        $this->avitoOffer = $offer;

        return $this;
    }

    /**
     * @return AvitoOffer|null
     */
    public function getAvitoOffer()
    {
        return $this->avitoOffer;
    }

    public function updateBaseInfo(ProductInfoRequest $request)
    {
        $this->url = $request->url;
        $this->idOnSite = $request->idOnSite;
        $this->isFail = $request->isFail;
        $this->site = $request->site;
        $this->offerCreatedAt = $request->createdAt;

        $this->updateModifiedDatetime();
    }
}
