<?php

namespace ParsingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class Price
{
    /**
     * @ORM\Column(nullable=true, type="integer", options={"unsigned" = true})
     * @var int
     */
    private $value;

    /**
     * @ORM\Column(nullable=true, type="integer", options={"unsigned" = true})
     * @var int
     */
    private $decimal;

    /**
     * @ORM\Column(nullable=true, type="string")
     * @var string
     */
    private $currency;

    public function __construct($value, $decimal, $currency)
    {
        $this->value = $value;
        $this->decimal = $decimal;
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getDecimal()
    {
        return $this->decimal;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}