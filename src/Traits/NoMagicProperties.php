<?php

namespace Hallekamp\NoMagicProperties\Traits;

use ReflectionClass;
use ReflectionProperty;

/**
 * trait to disable some of the laravel magic to allow for property declaration.
 */
trait NoMagicProperties
{
    public function __construct(array $attributes = [])
    {
        $reflect = new ReflectionClass(self::class);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($props as $prop) {
            $propertyName = $prop->getName();
            unset($this->$propertyName);
        }
        parent::__construct($attributes);
    }
}
