<?php
class StructureCache {

    protected static $cacheDir;
    protected static $cacheExtension = '.cache';

    public static function __Init(){
        static::$cacheDir = Config::get('path.cache.structure');
    }

    private static function filename($key){
        return static::$cacheDir.$key.static::$cacheExtension;
    }

    public static function has($key){
        return file_exists(static::filename($key));
    }

    public static function get($key){
        if (static::has($key)) {
            return unserialize(file_get_contents(static::filename($key)));
        } else {
            return null;
        }
    }

    public static function set($key,$data){
        file_put_contents(static::filename($key),serialize($data));
    }

}