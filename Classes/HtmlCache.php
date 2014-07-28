<?php
class HtmlCache {

    protected static $cacheDir;
    protected static $cacheExtension = '.cache';


    protected static $on = true;
    protected static $loaded = false;

    public static function __Init(){
        static::$cacheDir = Config::get('path.cache.html');
    }

    public static function off(){
        static::$on = false;
    }

    public static function load($key){
        if (!Config::get('cache.active')){
            return;
        }
        $filename = static::filename($key);
        if (file_exists($filename)){
            static::$loaded = true;
            Response::Output(file_get_contents($filename));
            die();
        }
    }

    public static function save($data){
        if (!Config::get('cache.active')){
            return;
        }
        if (!static::$loaded && empty($_POST) && static::$on){
            $key = Request::getInstance()->cacheKey;
            $filename = static::filename($key);
            if (!file_exists($filename)){
                file_put_contents($filename,$data);
            }
        }
    }

    private static function filename($key){
        return static::$cacheDir.$key.static::$cacheExtension;
    }

}