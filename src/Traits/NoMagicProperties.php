<?php

namespace Hallekamp\NoMagicProperties\Traits;

use Hallekamp\NoMagicProperties\ModelCache;
use Illuminate\Database\Eloquent\Model;
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
        ModelCache::restore();
        if (!isset(ModelCache::$modelCache[static::class])) {
            $this->walkModel(static::class);
            $parent = get_parent_class(static::class);
            if (!isset(ModelCache::$modelCache[$parent])) {
                $this->walkModel(static::class, $parent);
            }
        }

        foreach (ModelCache::$modelCache[static::class]['props'] as $prop) {
//            $propertyName = $prop->getName();

            // delete only properties that are declared in local model
//            echo $propertyName . ":\t" . $prop->getDeclaringClass()->getName() . "\t" . static::class . "\n";
            if ($prop['class'] === static::class) {
                $propertyName = $prop['name'];

                if (in_array($propertyName . "_id", ModelCache::$modelCache[static::class]['columns'])) {
                    // unset relation property
                    unset($this->$propertyName);
                    $propertyName = $propertyName . "_id";
                    if (in_array($propertyName, $this->fillable)) {
                        continue;
                    }
                } elseif (in_array($propertyName, ModelCache::$modelCache[static::class]['methods'])) {
                    // many to many relations
                    unset($this->$propertyName);
                    continue;
                } elseif (strtolower($propertyName) != $propertyName) {
                    if (in_array(
                        "get" . ucfirst($propertyName) . "Attribute",
                        ModelCache::$modelCache[static::class]['methods'])
                    ) {
                        unset($this->$propertyName);
                    } else {
                        $propertyName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName));
                        if (in_array($propertyName, ModelCache::$modelCache[static::class]['columns'])) {
                            unset($this->$propertyName);
                        }
                    }
                    continue;
                } elseif (!in_array($propertyName, ModelCache::$modelCache[static::class]['columns'])) {
                    // mutations
                    if (in_array(
                        "get" . ucfirst($propertyName) . "Attribute",
                        ModelCache::$modelCache[static::class]['methods'])
                    ) {
                        unset($this->$propertyName);
                    } elseif (in_array(
                        "set" . ucfirst($propertyName) . "Attribute",
                        ModelCache::$modelCache[static::class]['methods'])
                    ) {
                        unset($this->$propertyName);
                    }
                    continue;
                }
                if (!in_array($propertyName, $this->fillable)) {
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

    private function walkModel($model, $parent = false)
    {
        if ($parent !== false) {
            $this->walkModel($parent);
        }
        if (!isset(ModelCache::$modelCache[$model])) {
            $reflect = new ReflectionClass($model);
            $props = [];
            foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
                $class = $prop->getDeclaringClass()->getName();
                if ($class !== $parent && $class !== Model::class) {
                    $props[] = [
                        'name' => $prop->getName(),
                        'class' => $class,
                    ];
                }
            }
            if ($parent !== false) {
                $methods = array_filter($reflect->getMethods(ReflectionMethod::IS_PUBLIC), function ($method) use ($parent) {
                    return (
                        !in_array($method->getName(), ModelCache::$modelCache[$parent]['methods']) &&
                        !in_array($method->getName(), ModelCache::$modelCache[Model::class]['methods'])
                    );
                });
            } else {
                $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
            }
            $columns = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
            ModelCache::$modelCache[$model] = [
                'props' => $props,
                'methods' => array_map(function ($method) {
                    return $method->getName();
                }, $methods),
                'columns' => $columns,
            ];

            ModelCache::save();
        }
    }
}
