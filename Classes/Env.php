<?php
class Env {

    const DEV = 'DEV';
    const PROD = 'PROD';

    protected static $env = '';


    public static function __Init(){
        static::$env = static::PROD;
        if (preg_match('/\.local(host)?$/',$_SERVER['SERVER_NAME'])===1) {
            static::$env = static::DEV;
        }
    }

    public static function get(){
        return static::$env;
    }

    public static function isProd(){
        return static::$env == static::PROD;
    }

    public static function isDev(){
        return static::$env == static::DEV;
    }

}