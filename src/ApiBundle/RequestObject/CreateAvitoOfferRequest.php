<?php

namespace ApiBundle\RequestObject;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class CreateAvitoOfferRequest implements IRequestObject
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $price;

    /**
     * @var string[]
     */
    public $photos;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $phone;

    /**
     * @return Constraint[]
     */
    public function rules()
    {
        return [
            'photos' => [
                new Type(['type' => 'array']),
            ],

            'price' => [
                new Type(['type' => 'numeric']),
            ],

            'title' => [
                new NotBlank(),
                new Type(['type' => 'string']),
                new Length(['max' => 255]),
            ],

            'description' => [
                new Type(['type' => 'string']),
                new Length(['max' => 1000]),
            ],


            'username' => [
                new Type(['type' => 'string']),
                new Length(['max' => 255]),
            ],


            'phone' => [
                new Type(['type' => 'string']),
                new Length(['max' => 255]),
            ],
        ];
    }
}