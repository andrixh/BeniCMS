<?php
class Config{
    protected static $conf = array();

    public static function __Init(){
        static::$conf = include($_SERVER['DOCUMENT_ROOT'].'/Config/conf.php');
        static::$conf = array_merge(static::$conf, include($_SERVER['DOCUMENT_ROOT'].'/Config/privateconf.php'));

        if (Env::isDev()){
            static::$conf = array_merge(static::$conf, include($_SERVER['DOCUMENT_ROOT'].'/Config/privateconf_DEV.php'));
        }
    }


    public static function get($key){
        if (array_key_exists($key,static::$conf)) {
            return static::$conf[$key];
        } else {
            user_error('Config key: '.$key.' not found',E_USER_ERROR);
        }
    }

}