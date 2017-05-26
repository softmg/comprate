<?php

namespace ApiBundle\Constraints;


use ApiBundle\Validator\InstanceOfValidator;
use Symfony\Component\Validator\Constraint;

class InstanceOfConstraint extends Constraint
{
    public $className;

    public $message = 'The value "{{ value }}" is not instance of "{{ className }}".';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return InstanceOfValidator::class;
    }
}
