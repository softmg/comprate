<?php

namespace ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ParsingBundle\Entity\ParsingProductInfo;
use ParsingBundle\Entity\ParsingSite;
use ProductBundle\RequestObjects\CreateOfferRequest;
use ProductBundle\RequestObjects\UpdateOfferTypeAndSiteRequest;

/**
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\OfferRepo")
 */
class Offer implements IToArray
{
    use ToArray;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Product", inversedBy="offers")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\Embedded(class="ProductBundle\Entity\AvitoOffer")
     * @var AvitoOffer
     */
    private $avitoOffer;

    /**
     * @ORM\Embedded(class="ParsingBundle\Entity\ParsingProductInfo")
     * @var ParsingProductInfo
     */
    private $productInfo;

    /**
     * @var ArrayCollection|ProductAttribute[]
     */
    private $attributes;

    /**
     * @var ProductType
     *
     * @ORM\ManyToOne(targetEntity="ProductType")
     */
    private $type;

    /**
     * @var ParsingSite
     *
     * @ORM\ManyToOne(targetEntity="ParsingBundle\Entity\ParsingSite")
     */
    private $site;

    public function __construct(CreateOfferRequest $request)
    {
        $this->attributes = $request->attributes instanceof ArrayCollection ? $request->attributes : new ArrayCollection($request->attributes);
        $this->product = $request->product;
        $this->productInfo = $request->productInfo;
        $this->avitoOffer = $request->avitoOffer;
        $this->site = $request->site;
        $this->type = $request->type;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Product
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @return AvitoOffer
     */
    public function getAvitoOffer(): ?AvitoOffer
    {
        return $this->avitoOffer;
    }

    /**
     * @return ParsingProductInfo
     */
    public function getProductInfo(): ?ParsingProductInfo
    {
        return $this->productInfo;
    }

    /**
     * @return ArrayCollection|ProductAttribute
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return ProductType
     */
    public function getType(): ?ProductType
    {
        return $this->type;
    }

    /**
     * @return ParsingSite
     */
    public function getSite(): ?ParsingSite
    {
        return $this->site;
    }

    public function setProduct(?Product $product)
    {
        $this->product = $product;
    }

    public function setAvitoOffer(?AvitoOffer $avitoOffer)
    {
        $this->avitoOffer = $avitoOffer;
        
        $this->getProductInfo()->setIsFail(false);
    }

    /**
     * @param ParsingProductInfo $productInfo
     */
    public function setProductInfo(?ParsingProductInfo $productInfo)
    {
        $this->productInfo = $productInfo;
    }

    public function updateTypeAndSite(UpdateOfferTypeAndSiteRequest $request)
    {
        $this->site = $request->site;
        $this->type = $request->type;
    }
}