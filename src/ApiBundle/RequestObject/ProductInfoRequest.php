<?php

namespace ApiBundle\RequestObject;


use ApiBundle\Constraints\InstanceOfConstraint;
use ApiBundle\Constraints\ServiceMethod;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ProductInfoRequest implements IRequestObject
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $price;

    /**
     * @var bool
     */
    public $isFail;

    /**
     * @var string
     */
    public $idOnSite;

    /**
     * @var \DateTime
     */
    public $createdAt;

    public function __construct()
    {
        $this->isFail = true;
    }

    /**
     * @return Constraint[]
     */
    public function rules()
    {
        return [
            'url' => [
                new NotBlank(['groups' => [
                    'create',
                    'Default',
                ]]),
            ],

            'title' => [
                new NotBlank(['groups' => [
                    'create',
                    'Default',
                ]]),
            ],

            'price' => [
                new NotBlank(['groups' => [
                    'create',
                    'Default',
                ]]),
                new Range(['min' => 0, 'groups' => [
                    'create',
                    'Default',
                ]]),
            ],

            'idOnSite' => [
                new NotBlank(['groups' => [
                    'create',
                    'Default',
                ]]),

                new ServiceMethod([
                    'groups' => [
                        'create',
                    ],

                    'service' => 'repo.offer',
                    'method' => 'findByIdOnSite',
                    'reverseCheck' => true,
                ]),
            ],

            'createdAt' => [
                new NotBlank(['groups' => [
                    'create',
                ]]),

                new InstanceOfConstraint([
                    'className' => \DateTime::class,
                ]),
            ],
        ];
    }
}