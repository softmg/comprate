<?php

namespace ProductBundle\RequestObjects;


use ApiBundle\Constraints\InstanceOfConstraint;
use ApiBundle\RequestObject\IRequestObject;
use Doctrine\Common\Collections\ArrayCollection;
use ParsingBundle\Entity\ParsingProductInfo;
use ParsingBundle\Entity\ParsingSite;
use ProductBundle\Entity\AvitoOffer;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\ProductAttribute;
use ProductBundle\Entity\ProductType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateOfferRequest implements IRequestObject
{
    /**
     * @var Product
     */
    public $product;

    /**
     * @var AvitoOffer
     */
    public $avitoOffer;

    /**
     * @var ParsingProductInfo
     */
    public $productInfo;

    /**
     * @var ArrayCollection|ProductAttribute[]
     */
    public $attributes;

    /**
     * @var ParsingSite
     */
    public $site;

    /**
     * @var ProductType
     */
    public $type;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    /**
     * @return Constraint[]
     */
    public function rules()
    {
        return [
            'productInfo' => [
                new NotBlank(),
                new InstanceOfConstraint(['className' => ParsingProductInfo::class]),
            ],

            'product' => [
                new InstanceOfConstraint(['className' => Product::class]),
            ],

            'avitoOffer' => [
                new InstanceOfConstraint(['className' => AvitoOffer::class]),
            ],

            'attributes' => [
                new Callback(['callback' => [$this, 'isArrayLike']]),
                new InstanceOfConstraint(['isCollection' => true, 'className' => ProductAttribute::class]),
            ],

            'site' => [
                new NotBlank(),
                new InstanceOfConstraint(['className' => ParsingSite::class]),
            ],

            'type' => [
                new NotBlank(),
                new InstanceOfConstraint(['className' => ProductType::class]),
            ],
        ];
    }

    public function isArrayLike($value)
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }
}