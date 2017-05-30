<?php

namespace ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ParsingBundle\Entity\ParsingProductInfo;
use ParsingBundle\Entity\ParsingSite;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ProductRepository")
 */
class Product implements IToArray
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var Vendor
     *
     * @ORM\ManyToOne(targetEntity="Vendor", inversedBy="products")
     * @ORM\JoinColumn(name="vendor_id", referencedColumnName="id")
     */
    private $vendor;

    /**
     * @var ProductType
     *
     * @ORM\ManyToOne(targetEntity="ProductType", inversedBy="products")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    private $type;

    /**
     * @var ParsingSite
     *
     * @ORM\ManyToOne(targetEntity="ParsingBundle\Entity\ParsingSite")
     */
    private $site;

    /**
     * @ORM\Embedded(class="ProductBundle\Entity\Price")
     * @var Price
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="is_actual", type="boolean", nullable=true)
     */
    private $isActual = true;

    /**
     * @ORM\OneToMany(targetEntity="ProductBundle\Entity\ProductAttribute", mappedBy="product", cascade={"persist"})
     */
    private $productAttributes;
    
    /**
     * @ORM\OneToOne(targetEntity="ProductBenchmark", mappedBy="product")
     */
    private $productBenchmark;
    
    /**
     * @ORM\OneToMany(targetEntity="ProductBundle\Entity\Offer", mappedBy="product")
     *
     * @var Offer[]|ArrayCollection
     */
    private $offers;

    /**
     * @ORM\Embedded(class="ParsingBundle\Entity\ParsingProductInfo")
     * @var ParsingProductInfo
     */
    private $productInfo;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->productAttributes = new ArrayCollection();
        $this->offers = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Product
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
     * Set vendor
     *
     * @param Vendor $vendor
     *
     * @return Product
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * Get vendor
     *
     * @return Vendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Set type
     *
     * @param ProductType $type
     *
     * @return Product
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return ProductType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Price
     */
    public function getPrice(): ?Price
    {
        return $this->price;
    }

    /**
     * @param Price $price
     */
    public function setPrice(?Price $price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getIsActual()
    {
        return $this->isActual;
    }

    /**
     * @param mixed $isActual
     */
    public function setIsActual($isActual)
    {
        $this->isActual = $isActual;
    }

    /**
     * @return mixed
     */
    public function getProductAttributes()
    {
        return $this->productAttributes;
    }

    /**
     * @return string
     */
    public function getProductAttributesString()
    {
        $res = [];
        /** @var ProductAttribute $attr */
        foreach ($this->getProductAttributes() as $attr) {
            $res[] = $attr->getValue();
        }
        return join(", ", $res);
    }
    
    /**
     * @param ProductAttribute $productAttribute
     * @return Product
     */
    public function addProductAttribute($productAttribute)
    {
        $productAttribute->setProduct($this);
        $this->productAttributes->add($productAttribute);
        return $this;
    }

    /**
     * @param mixed $productAttributes
     */
    public function setProductAttributes($productAttributes)
    {
        $this->productAttributes = $productAttributes;
    }
    
    /**
     * @return mixed
     */
    public function getProductBenchmark()
    {
        return $this->productBenchmark;
    }
    
    /**
     * @param mixed $productBenchmark
     */
    public function setProductBenchmark($productBenchmark)
    {
        $this->productBenchmark = $productBenchmark;
    }
    
    /**
     * @return Offer[]|ArrayCollection
     */
    public function getOffers()
    {
        return $this->offers;
    }
    
    /**
     * @param Offer[]|ArrayCollection $offers
     */
    public function setOffers($offers)
    {
        $this->offers = new ArrayCollection($offers);
    }

    /**
     * @param Offer $offer
     *
     * @return Product
     */
    public function addOffer(Offer $offer)
    {
        $this->offers->add($offer);

        $offer->setProduct($this);

        return $this;
    }

    /**
     * @return ParsingSite
     */
    public function getSite(): ?ParsingSite
    {
        return $this->site;
    }

    /**
     * @param ParsingSite $site
     */
    public function setSite(?ParsingSite $site)
    {
        $this->site = $site;
    }

    /**
     * @return ParsingProductInfo
     */
    public function getProductInfo(): ?ParsingProductInfo
    {
        return $this->productInfo;
    }

    /**
     * @param ParsingProductInfo $productInfo
     */
    public function setProductInfo(?ParsingProductInfo $productInfo)
    {
        $this->productInfo = $productInfo;
    }
}
