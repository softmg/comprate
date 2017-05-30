<?php

namespace ProductBundle\Entity;


trait ToArray
{
    public function toArray(): array {
        $result = [];

        $ref = new \ReflectionClass(static::class);

        $properties = $ref->getProperties();

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $property->setAccessible(true);

            $propertyName = $property->getName();
            $value = $property->getValue($this);

            if (is_object($value) && method_exists($value, 'toArray')) {
                $value = $value->toArray();
            }

            $result[$propertyName] = $value;
        }

        return $result;
    }
}