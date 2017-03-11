<?php

namespace ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProductType
 *
 * @ORM\Table(name="product_type")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ProductTypeRepository")
 */
class ProductType
{
    const CPU = 'cpu';
    const MOTHERBOARD = 'motherboard';
    const VIDEOCARD = 'video-card';
    const MOBILE_ANDROID = 'mobile-android';
    const MOBILE_IOS = 'mobile-ios';
    const MEMORY = 'memory';
    const STORAGE = 'internal-hard-drive';

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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="productType")
     */
    private $products;

    /**
     * @ORM\ManyToMany(targetEntity="Attribute", mappedBy="productTypes")
     */
    private $attributes;

    /**
     * ProductType constructor.
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->attributes = new ArrayCollection();
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
     * @return ProductType
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
     * Set code
     *
     * @param string $code
     *
     * @return ProductType
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param Attribute $attribute
     *
     * @return ProductType
     */
    public function addAttribute($attribute)
    {
        $this->attributes[]= $attribute;

        return $this;
    }

    /**
     * @param Attribute $attribute
     *
     * @return ProductType
     */
    public function removeAttribute($attribute)
    {
        $this->attributes->removeElement($attribute);

        return $this;
    }
}
