<?php

namespace Hallekamp\NoMagicProperties\Traits;

use Hallekamp\NoMagicProperties\ModelCache;
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
        if(empty(ModelCache::$modelCache[static::class])){
            file_put_contents(storage_path('modelcache.log'),'cache miss for '.static::class."\n");
            $reflect = new ReflectionClass(self::class);
            $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

            $columns = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
            ModelCache::$modelCache[static::class] = [
                'props' => $props,
                'columns' => $columns
            ];

        }
        foreach (ModelCache::$modelCache[static::class]['props'] as $prop) {
            $propertyName = $prop->getName();
            // delete only properties that are declared in local model
            if ($prop->getDeclaringClass()->getName() === self::class) {
                if (!in_array($propertyName, $this->fillable)){
                    if(in_array($propertyName."_id",ModelCache::$modelCache[static::class]['columns'])){
                        $propertyName = $propertyName."_id";
                        if (in_array($propertyName, $this->fillable)){
                            continue;
                        }
                    }
                    $this->fillable[] = $propertyName;
                }
                unset($this->$propertyName);
            }
        }

        parent::__construct($attributes);
    }
}
