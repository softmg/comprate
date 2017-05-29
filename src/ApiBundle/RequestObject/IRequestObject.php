<?php

namespace ApiBundle\RequestObject;


use Symfony\Component\Validator\Constraint;

interface IRequestObject
{
    /**
     * @return Constraint[]
     */
    public function rules();
}