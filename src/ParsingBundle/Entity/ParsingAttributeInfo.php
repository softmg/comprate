<?php

namespace ParsingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\ProductType;

/**
 * ParsingAttributeInfo
 *
 * @ORM\Table(name="parsing_attribute_info")
 * @ORM\Entity()
 */
class ParsingAttributeInfo
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
     * @var Attribute
     *
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    private $attribute;

    /**
     * @var ProductType
     *
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\ProductType")
     * @ORM\JoinColumn(name="product_type_id", referencedColumnName="id")
     */
    private $productType;

    /**
     * @var ParsingSite
     *
     * @ORM\ManyToOne(targetEntity="ParsingSite")
     * @ORM\JoinColumn(name="parsing_site_id", referencedColumnName="id")
     */
    private $site;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
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
     * Set attribute
     *
     * @param Attribute $attribute
     *
     * @return ParsingAttributeInfo
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
     * Set site
     *
     * @param ParsingSite $site
     *
     * @return ParsingAttributeInfo
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
     * Set name
     *
     * @param string $name
     *
     * @return ParsingAttributeInfo
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
     * @return \ProductBundle\Entity\ProductType
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * @param \ProductBundle\Entity\ProductType $productType
     */
    public function setProductType($productType)
    {
        $this->productType = $productType;
    }
}
