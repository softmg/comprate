<?php

namespace ProductBundle\RequestObjects;


use ApiBundle\RequestObject\IRequestObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class GetOneOfferRequest implements IRequestObject
{
    /**
     * @var string
     */
    public $idOnSite;

    /**
     * @return Constraint[]
     */
    public function rules()
    {
        return [
            'idOnSite' => [
                new NotBlank([
                    'groups' => [
                        'id-on-site-is-required',
                    ],
                ]),

                new Type(['type' => 'string']),
            ],
        ];
    }
}