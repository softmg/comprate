<?php

namespace ApiBundle\Validator;


use ApiBundle\Constraints\InstanceOfConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InstanceOfValidator extends ConstraintValidator
{

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value || !$constraint instanceof InstanceOfConstraint) {
            return;
        }

        if (
            !is_object($value)
            || (
                !is_a($value, $constraint->className, true)
                && !array_key_exists($constraint->className, class_uses($value))
                && !array_key_exists($constraint->className, class_implements($value))
            )
        ) {
            $this
                ->context
                ->addViolation($constraint->message, [
                    '{{ className }}' => $constraint->className,
                    '{{ value }}' => $value,
                ]);
        }
    }
}