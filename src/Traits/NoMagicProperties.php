<?php

namespace Hallekamp\NoMagicProperties\Traits;

use Hallekamp\NoMagicProperties\ModelCache;
use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;

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
        if (empty(ModelCache::$modelCache[static::class])) {
//            file_put_contents(storage_path('modelcache.log'),'cache miss for '.static::class."\n");
            $reflect = new ReflectionClass(static::class);
            $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
            $columns = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
            ModelCache::$modelCache[static::class] = [
                'props' => $props,
                'methods' => array_map(function ($method) {
                    return $method->getName();
                }, $methods),
                'columns' => $columns,
            ];

        }
        foreach (ModelCache::$modelCache[static::class]['props'] as $prop) {
            $propertyName = $prop->getName();
            // delete only properties that are declared in local model
//            echo $propertyName . ":\t" . $prop->getDeclaringClass()->getName() . "\t" . static::class . "\n";
            if ($prop->getDeclaringClass()->getName() === static::class) {
                if (!in_array($propertyName, $this->fillable)) {
//                    echo "property $propertyName not in fillable\n";
                    if (in_array($propertyName . "_id", ModelCache::$modelCache[static::class]['columns'])) {
                        // unset relation property
                        unset($this->$propertyName);
                        $propertyName = $propertyName . "_id";
                        if (in_array($propertyName, $this->fillable)) {
                            continue;
                        }
                    } elseif (strtolower($propertyName) != $propertyName) {
                        if (in_array(
                            "get" . ucfirst($propertyName) . "Attribute",
                            ModelCache::$modelCache[static::class]['methods'])
                        ) {
                            unset($this->$propertyName);
                        }
                        $propertyName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName));
                        if (in_array($propertyName, ModelCache::$modelCache[static::class]['columns'])) {

                            unset($this->$propertyName);
                        }
                        continue;
                    } elseif (!in_array($propertyName, ModelCache::$modelCache[static::class]['columns'])) {
                        if (in_array(
                            "get" . ucfirst($propertyName) . "Attribute",
                            ModelCache::$modelCache[static::class]['methods'])
                        ) {
                            unset($this->$propertyName);
                        }
                        continue;
                    }
//                    echo "add $propertyName to fillable";
                    $this->fillable[] = $propertyName;
                }
                unset($this->$propertyName);
            }
        }
        if (empty($this->fillable)) {
            $this->fillable = ModelCache::$modelCache[static::class]['columns'];
        }

        parent::__construct($attributes);
    }
}
