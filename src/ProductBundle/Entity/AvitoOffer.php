<?php

namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvitoOffer
 *
 * @ORM\Table(name="avito_offer")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\AvitoOfferRepository")
 */
class AvitoOffer
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
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=1000, unique=true)
     */
    private $description;
    
    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $price;
    
    /**
     * @var string
     *
     * @ORM\Column(name="photos", type="string", length=1000, unique=true)
     */
    private $photos;
    
    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     */
    private $username;
    
    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, unique=true)
     */
    private $phone;
    
    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="avitoOffers")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;
    
    /**
     * @var \ParsingBundle\Entity\ParsingProductInfo
     *
     * @ORM\ManyToOne(targetEntity="ParsingBundle\Entity\ParsingProductInfo", inversedBy="avitoOffers")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $parsingProductInfo;

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
     * @return AvitoOffer
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
     * Set product
     *
     * @param Product $product
     *
     * @return AvitoOffer
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
     * Set price
     *
     * @param string $price
     *
     * @return AvitoOffer
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    /**
     * @return string
     */
    public function getPhotos()
    {
        return $this->photos;
    }
    
    /**
     * @param string $photos
     */
    public function setPhotos($photos)
    {
        $this->photos = $photos;
    }
    
    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }
    
    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
    
    /**
     * @return \ParsingBundle\Entity\ParsingProductInfo
     */
    public function getParsingProductInfo()
    {
        return $this->parsingProductInfo;
    }
    
    /**
     * @param \ParsingBundle\Entity\ParsingProductInfo $parsingProductInfo
     */
    public function setParsingProductInfo($parsingProductInfo)
    {
        $this->parsingProductInfo = $parsingProductInfo;
    }
}
