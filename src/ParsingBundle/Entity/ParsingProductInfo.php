<?php

namespace ParsingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ProductBundle\Entity\Product;

/**
 * ParsingProductInfo
 *
 * @ORM\Table(name="parsing_product_info")
 * @ORM\Entity(repositoryClass="ParsingBundle\Repository\ParsingProductInfoRepository")
 */
class ParsingProductInfo
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
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var ParsingSite
     *
     * @ORM\ManyToOne(targetEntity="ParsingSite")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     */
    private $site;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;


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
     * @param string $product
     *
     * @return ParsingProductInfo
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set site
     *
     * @param integer $site
     *
     * @return ParsingProductInfo
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return int
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return ParsingProductInfo
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}

