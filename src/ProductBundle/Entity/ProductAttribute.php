<?php

namespace ProductBundle\Entity;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProductAttribute
 *
 * @ORM\Table(name="product_attribute")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ProductAttributeRepository")
 */
class ProductAttribute
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
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productAtributes")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var Attribute
     *
     * @ORM\ManyToOne(targetEntity="Attribute", inversedBy="productAtributes")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    private $attribute;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="max_value", type="integer", nullable=true)
     */
    private $maxValue;

    /**
     * @var int
     *
     * @ORM\Column(name="max_number", type="integer", nullable=true)
     */
    private $maxNumber;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_required", type="boolean", nullable=true)
     */
    private $isRequired = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_required_choice", type="boolean", nullable=true)
     */
    private $isRequiredChoice = false;


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
     * @return ProductAttribute
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
     * Set attribute
     *
     * @param Attribute $attribute
     *
     * @return ProductAttribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return ProductAttribute
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set maxValue
     *
     * @param integer $maxValue
     *
     * @return ProductAttribute
     */
    public function setMaxValue($maxValue)
    {
        $this->maxValue = $maxValue;

        return $this;
    }

    /**
     * Get maxValue
     *
     * @return integer
     */
    public function getMaxValue()
    {
        return $this->maxValue;
    }

    /**
     * Set maxNumber
     *
     * @param integer $maxNumber
     *
     * @return ProductAttribute
     */
    public function setMaxNumber($maxNumber)
    {
        $this->maxNumber = $maxNumber;

        return $this;
    }

    /**
     * Get maxNumber
     *
     * @return int
     */
    public function getMaxNumber()
    {
        return $this->maxNumber;
    }

    /**
     * Set isRequired
     *
     * @param boolean $isRequired
     *
     * @return ProductAttribute
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    /**
     * Get isRequired
     *
     * @return bool
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * Set isRequiredChoice
     *
     * @param boolean $isRequiredChoice
     *
     * @return ProductAttribute
     */
    public function setIsRequiredChoice($isRequiredChoice)
    {
        $this->isRequiredChoice = $isRequiredChoice;

        return $this;
    }

    /**
     * Get isRequiredChoice
     *
     * @return bool
     */
    public function getIsRequiredChoice()
    {
        return $this->isRequiredChoice;
    }
}
