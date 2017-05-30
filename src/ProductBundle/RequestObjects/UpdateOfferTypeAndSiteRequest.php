<?php

namespace ProductBundle\RequestObjects;


use ApiBundle\Constraints\InstanceOfConstraint;
use ApiBundle\RequestObject\IRequestObject;
use ParsingBundle\Entity\ParsingSite;
use ProductBundle\Entity\ProductType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateOfferTypeAndSiteRequest implements IRequestObject
{
    /**
     * @var ProductType
     */
    public $type;

    /**
     * @var ParsingSite
     */
    public $site;

    /**
     * @return Constraint[]
     */
    public function rules()
    {
        return [
            'site' => [
                new NotBlank(),
                new InstanceOfConstraint(['className' => ParsingSite::class]),
            ],

            'type' => [
                new NotBlank(),
                new InstanceOfConstraint(['className' => ParsingSite::class]),
            ],
        ];
    }
}