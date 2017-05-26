<?php

namespace ApiBundle\Constraints;


use ApiBundle\Validator\ServiceMethodValidator;
use Symfony\Component\Validator\Constraint;

class ServiceMethod extends Constraint
{
    public $service;

    public $method;

    public $message = 'Service "{{ service }}" method "{{ method }}" returns {{ checkType }} when invoked with parameter "{{ value }}".';

    public $reverseCheck = false;

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return ServiceMethodValidator::class;
    }
}