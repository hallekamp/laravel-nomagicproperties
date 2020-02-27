<?php

namespace Hallekamp\NoMagicProperties\Traits;

use ReflectionClass;
use ReflectionProperty;

/**
 * trait to disable some of the laravel magic to allow for property declaration.
 */
trait NoMagicProperties
{
    /**
     * NoMagicProperties constructor.
     *
     * Delete declared properties prior to laravel initializing magic
     * @param array $attributes
     * @throws \ReflectionException
     */
    public function __construct(array $attributes = [])
    {
        $reflect = new ReflectionClass(self::class);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($props as $prop) {
            $propertyName = $prop->getName();
            // delete only properties that are declared in local model
            if ($prop->getDeclaringClass()->getName() === self::class) {
                if (!in_array($propertyName, $this->fillable)){
                    $this->fillable[] = $propertyName;
                }
                unset($this->$propertyName);
            }
        }

        parent::__construct($attributes);
    }
}
