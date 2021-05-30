<?php

namespace Hallekamp\NoMagicProperties;

use \Illuminate\Support\Facades\Cache;

class ModelCache
{
    /**
     * simple in memory cache
     * @var array
     */
    public static $modelCache = [];
    public static $modelCacheInit = false;

    /**
     * cache time to live.
     * default 1 week.
     *
     * @var int
     */
    public static $ttl = 604800;

    public static function restore()
    {
        if(static::$modelCacheInit === false){
            if (in_array(config('cache.default'), [
                'redis',
                'predis',
                'phpredis',
            ])) {
                static::$modelCache = Cache::get('lnmp_mc');
                static::$modelCacheInit = true;
            }
        }
    }

    public static function save()
    {
        if (in_array(config('cache.default'), [
            'redis',
            'predis',
            'phpredis',
        ])) {
            Cache::put('lnmp_mc', static::$modelCache, static::$ttl);
        }
    }

    public static function clear($channel)
    {
        if ($channel) {
            static::$modelCache[$channel] = [];
        } else {
            static::$modelCache = [];
            static::$modelCacheInit = false;
        }
        if (in_array(config('cache.default'), [
            'redis',
            'predis',
            'phpredis',
        ])) {
            Cache::forget('lnmp_mc');
        }
    }
}
