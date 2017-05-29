<?php

namespace ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ParsingBundle\Entity\ParsingProductInfo;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ProductRepository")
 */
class Product
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
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="is_actual", type="boolean", nullable=true)
     */
    private $isActual = true;

    /**
     * @ORM\OneToMany(targetEntity="ProductAttribute", mappedBy="product", cascade={"persist"})
     */
    private $productAttributes;
    
    /**
     * @ORM\OneToOne(targetEntity="ProductBenchmark", mappedBy="product")
     */
    private $productBenchmark;
    
    /**
     * @ORM\OneToMany(targetEntity="ParsingBundle\Entity\ParsingProductInfo", mappedBy="product")
     *
     * @var ParsingProductInfo[]|ArrayCollection
     */
    private $offers;

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
     * Set price
     *
     * @param string $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
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
     * @return ParsingProductInfo[]|ArrayCollection
     */
    public function getOffers()
    {
        return $this->offers;
    }
    
    /**
     * @param ParsingProductInfo[]|ArrayCollection $offers
     */
    public function setOffers($offers)
    {
        $this->offers = new ArrayCollection($offers);
    }

    /**
     * @param ParsingProductInfo $offer
     *
     * @return Product
     */
    public function addOffer(ParsingProductInfo $offer)
    {
        $this->offers->add($offer);

        $offer->setProduct($this);

        return $this;
    }
}
