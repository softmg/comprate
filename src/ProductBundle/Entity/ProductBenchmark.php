<?php

namespace ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="product_benchmark")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ProductBenchmarkRepository")
 */
class ProductBenchmark
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
     * @var int
     *
     * @ORM\Column(name="rate", type="integer", nullable=true)
     */
    private $rate;

    /**
     * @var string
     *
     * @ORM\Column(name="rateInfo", type="string", length=255, nullable=true)
     */
    private $rateInfo;
    
    /**
     * @var Product
     *
     * @ORM\OneToOne(targetEntity="Product", inversedBy="productBenchmark")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

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
     * @return ProductBenchmark
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
     * @return ProductBenchmark
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
     * @return ProductBenchmark
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
     * Set product
     *
     * @param Product $product
     *
     * @return ProductBenchmark
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
     * Set rate
     *
     * @param integer $rate
     *
     * @return ProductBenchmark
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return int
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set rateInfo
     *
     * @param string $rateInfo
     *
     * @return ProductBenchmark
     */
    public function setRateInfo($rateInfo)
    {
        $this->rateInfo = $rateInfo;

        return $this;
    }

    /**
     * Get rateInfo
     *
     * @return string
     */
    public function getRateInfo()
    {
        return $this->rateInfo;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return ProductBenchmark
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
     * @return ProductBenchmark
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
}
