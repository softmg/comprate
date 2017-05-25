<?php

namespace ApiBundle\RequestObject;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductInfoRequest implements IRequestObject
{

    public $url;

    /**
     * @return Constraint[]
     */
    public function rules()
    {
        return [
            'url' => [
                new NotBlank(),
            ]
        ];
    }
}