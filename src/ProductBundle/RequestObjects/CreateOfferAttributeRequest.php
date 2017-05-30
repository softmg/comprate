<?php

namespace ProductBundle\RequestObjects;


use ApiBundle\Constraints\InstanceOfConstraint;
use ApiBundle\RequestObject\IRequestObject;
use ProductBundle\Entity\AttributeValue;
use ProductBundle\Entity\Offer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class CreateOfferAttributeRequest implements IRequestObject
{
    /**
     * @var Offer
     */
    public $offer;

    /**
     * @var AttributeValue
     */
    public $attribute;

    /**
     * @var int
     */
    public $compatibility;

    /**
     * @return Constraint[]
     */
    public function rules()
    {
        return [
            'attribute' => [
                new NotBlank(),
                new InstanceOfConstraint([
                    'className' => AttributeValue::class,
                ])
            ],

            'offer' => [
                new NotBlank(),
                new InstanceOfConstraint([
                    'className' => Offer::class,
                ]),
            ],

            'compatibility' => [
                new NotBlank(),
                new Range([
                    'min' => 0,
                    'max' => 100,
                ]),
            ],
        ];
    }
}