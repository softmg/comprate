<?php

namespace ParsingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ProductBundle\Entity\Attribute;

/**
 * ParsingAttributeInfo
 *
 * @ORM\Table(name="parsing_attribute_info")
 * @ORM\Entity(repositoryClass="ParsingBundle\Repository\ParsingAttributeInfoRepository")
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
    private $attributeId;


    /**
     * @var ParsingSite
     *
     * @ORM\ManyToOne(targetEntity="ParsingSite")
     * @ORM\JoinColumn(name="parsing_site_id", referencedColumnName="id")
     */
    private $parsingSiteId;

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
     * Set attributeId
     *
     * @param integer $attributeId
     *
     * @return ParsingAttributeInfo
     */
    public function setAttributeId($attributeId)
    {
        $this->attributeId = $attributeId;

        return $this;
    }

    /**
     * Get attributeId
     *
     * @return int
     */
    public function getAttributeId()
    {
        return $this->attributeId;
    }

    /**
     * Set parsingSiteId
     *
     * @param integer $parsingSiteId
     *
     * @return ParsingAttributeInfo
     */
    public function setParsingSiteId($parsingSiteId)
    {
        $this->parsingSiteId = $parsingSiteId;

        return $this;
    }

    /**
     * Get parsingSiteId
     *
     * @return int
     */
    public function getParsingSiteId()
    {
        return $this->parsingSiteId;
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
}
