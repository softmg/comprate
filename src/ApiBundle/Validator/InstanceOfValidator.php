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
        if (!$value || !$constraint instanceof InstanceOfConstraint || ($constraint->isCollection && !$this->isArrayLike($value))) {
            return;
        }

        if ($constraint->isCollection) {
            foreach ($value as $item) {
                if ($this->isInstanceOf($item, $constraint->className)) {
                    continue;
                }

                $this->addViolation($this->valueToString($item), $constraint);
            }

            return;
        }

        if (!$this->isInstanceOf($value, $constraint->className)) {
            $this->addViolation($this->valueToString($value), $constraint);
        }
    }

    private function valueToString($value)
    {
        if (is_object($value)) {
            return get_class($value);
        }

        return (string)$value;
    }

    private function addViolation(string $value, InstanceOfConstraint $constraint)
    {
        $this
            ->context
            ->addViolation($constraint->message, [
                '{{ className }}' => $constraint->className,
                '{{ value }}' => $value,
            ]);
    }

    private function isInstanceOf($value, string $className)
    {
        return is_object($value) &&
            (
                is_a($value, $className, true)
                || array_key_exists($className, class_uses($value))
                || array_key_exists($className, class_implements($value))
            );
    }

    private function isArrayLike($value): bool
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }
}