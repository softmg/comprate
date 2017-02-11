<?php

namespace ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute
 *
 * @ORM\Table(name="atrribute")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\AttributeRepository")
 */
class Attribute
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity="ProductAttribute", mappedBy="attribute")
     */
    private $productAttributes;

    /**
     * @var ProductType[]
     * @ORM\ManyToMany(targetEntity="ProductType", inversedBy="attributes")
     * @ORM\JoinTable(name="attribute_types")
     */
    private $productTypes;

    /**
     * @ORM\OneToMany(targetEntity="AttributeValue", mappedBy="attribute")
     */
    private $values;

    /**
     * Attribute constructor.
     */
    public function __construct()
    {
        $this->productAttributes = new ArrayCollection();
        $this->productTypes = new ArrayCollection();
        $this->values = new ArrayCollection();
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
     * @return Attribute
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
     * @return Attribute
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
     * @return \ProductBundle\Entity\ProductType[]
     */
    public function getProductTypes()
    {
        return $this->productTypes;
    }

    /**
     * @param \ProductBundle\Entity\ProductType[] $productTypes
     */
    public function setProductTypes($productTypes)
    {
        $this->productTypes = $productTypes;
    }

    /**
     * Set value
     *
     * @param ProductType $productType
     *
     * @return Attribute
     */
    public function addProductType($productType)
    {
        $this->productTypes[]= $productType;

        return $this;
    }

    /**
     * Set value
     *
     * @param ProductType $productType
     *
     * @return Attribute
     */
    public function removeProductType($productType)
    {
        $this->productTypes->removeElement($productType);

        return $this;
    }

    /**
     * @return \ProductBundle\Entity\AttributeValue[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param \ProductBundle\Entity\AttributeValue[] $values
     *
     * @return Attribute
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @param AttributeValue $value
     *
     * @return Attribute
     */
    public function addValue($value)
    {
        $this->values[]= $value;

        return $this;
    }

    /**
     * @param AttributeValue $value
     *
     * @return Attribute
     */
    public function removeValue($value)
    {
        $this->values->removeElement($value);

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
