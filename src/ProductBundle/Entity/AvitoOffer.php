<?php

namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvitoOffer
 *
 * @ORM\Embeddable
 */
class AvitoOffer
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=1000, nullable=true)
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
     * @ORM\Column(name="photos", type="string", length=1000, nullable=true)
     */
    private $photos;
    
    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=true)
     */
    private $username;
    
    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;


    /**
     * Set name
     *
     * @param string|null $name
     *
     * @return AvitoOffer
     */
    public function setName($name = null)
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
     * @param string|null $description
     */
    public function setDescription($description = null)
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
}
