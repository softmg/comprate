<?php

namespace ComputerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Computer
 *
 * @ORM\Table(name="computer")
 * @ORM\Entity(repositoryClass="ComputerBundle\Repository\ComputerRepository")
 */
class Computer
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="rate", type="integer", nullable=true)
     */
    private $rate;

    /**
     * @var string
     *
     * @ORM\Column(name="user_ip", type="string", length=255, nullable=true)
     */
    private $userIp;
    
    
    /**
     * @var \ProductBundle\Entity\Product[]
     * @ORM\ManyToMany(targetEntity="\ProductBundle\Entity\Product", inversedBy="computers")
     * @ORM\JoinTable(name="computer_products")
     */
    private $products;
    
    /**
     * Computer constructor.
     */
    public function __construct()
    {
        $this->products = $products = new ArrayCollection();
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
     * @return Computer
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
     * Set rate
     *
     * @param integer $rate
     *
     * @return Computer
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
     * Set userIp
     *
     * @param string $userIp
     *
     * @return Computer
     */
    public function setUserIp($userIp)
    {
        $this->userIp = $userIp;

        return $this;
    }

    /**
     * Get userIp
     *
     * @return string
     */
    public function getUserIp()
    {
        return $this->userIp;
    }
    
    /**
     * @return \ProductBundle\Entity\Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }
    
    /**
     * @param \ProductBundle\Entity\Product[] $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }
    
    /**
     * @param \ProductBundle\Entity\Product $product
     *
     * @return Computer
     */
    public function addProduct($product)
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }
        
        return $this;
    }
    
    /**
     * @param \ProductBundle\Entity\Product $product
     *
     * @return Computer
     */
    public function removeProduct($product)
    {
        if ($this->products->contains($product)) {
            $this->products->remove($product);
        }
        
        return $this;
    }
}
