<?php
use Redis\RedisMultiStorage;
use Redis\RedisMultiCache;
class CRedis{

    public static function storage($app = 'web')
    {
        global $CONFIG;
        $app = strtolower(trim($app));
        if(empty($app) || !isset($CONFIG['RedisStorge'][$app])){
            throw new Exception("Undefined Redis storage App [{$app}]");
        }
        RedisMultiStorage::config($CONFIG['RedisStorge']);
        return RedisMultiStorage::getInstance($app);
    }



    public static function cache($app = 'web')
    {
        global $CONFIG;
        $app = strtolower(trim($app));
        if(empty($app) || !isset($CONFIG['RedisCache'][$app])){
            throw new Exception("Undefined Redis Cache App [{$app}]");
        }
        RedisMultiCache::config($CONFIG['RedisCache']);
        return RedisMultiCache::getInstance($app);
    }
}
