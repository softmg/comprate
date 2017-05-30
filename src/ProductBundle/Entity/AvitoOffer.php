<?php

namespace ProductBundle\Entity;

use ApiBundle\RequestObject\CreateAvitoOfferRequest;
use Doctrine\ORM\Mapping as ORM;
use ProductBundle\Entity\Price;

/**
 * AvitoOffer
 *
 * @ORM\Embeddable
 */
class AvitoOffer implements IToArray
{
    use ToArray;

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
     * @var string|Price
     *
     * @ORM\Embedded(class="ProductBundle\Entity\Price")
     */
    private $price;
    
    /**
     * @var string[]
     *
     * @ORM\Column(name="photos", type="array", nullable=true)
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

    public function __construct(CreateAvitoOfferRequest $request)
    {
        $this->phone = $request->phone;
        $this->photos = $request->photos;
        $this->username = $request->username;
        $this->name = $request->name;
        $this->description = $request->description;
        $this->price = $request->price;
    }

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
     * @param Price|string $price
     *
     * @return AvitoOffer
     */
    public function setPrice(Price $price = null)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return Price|null
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
     * @return string[]
     */
    public function getPhotos()
    {
        return $this->photos;
    }
    
    /**
     * @param string[] $photos
     */
    public function setPhotos(array $photos = null)
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

    public function addPhoto($photo)
    {
        $index = array_search($photo, $this->photos, true);

        if (false === $index) {
            return;
        }

        $this->photos[] = $photo;
    }

    public function removePhoto($photo)
    {
        $index = array_search($photo, $this->photos, true);

        if (false === $index) {
            return;
        }

        array_splice($this->photos, $index, 1);
    }
}
