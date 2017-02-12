<?php

namespace ComputerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ProductBundle\Entity\Product;

/**
 * ComputerProduct
 *
 * @ORM\Table(name="computer_product")
 * @ORM\Entity(repositoryClass="ComputerBundle\Repository\ComputerProductRepository")
 */
class ComputerProduct
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
     * @var Computer
     *
     * @ORM\ManyToOne(targetEntity="Computer")
     * @ORM\JoinColumn(name="computer_id", referencedColumnName="id")
     */
    private $computer;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Product")
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
     * Set computer
     *
     * @param Computer $computer
     *
     * @return ComputerProduct
     */
    public function setComputer($computer)
    {
        $this->computer = $computer;

        return $this;
    }

    /**
     * Get computer
     *
     * @return Computer
     */
    public function getComputer()
    {
        return $this->computer;
    }

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return ComputerProduct
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
}
