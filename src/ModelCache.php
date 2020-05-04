<?php

namespace Hallekamp\NoMagicProperties;

class ModelCache
{
    /* simple in memory cache */
    Public static $modelCache = [];

    public function clear($channel)
    {
        if ($channel) {
            self::$modelCache[$channel] = [];
        } else {
            self::$modelCache = [];
        }
    }
}
