<?php

class Response {
    const REDIRECT_PERM = 301;
    const REDIRECT_FOUND = 302;
    const REDIRECT_TEMP = 307;

    const HEADER_CONTENT_TYPE = 'Content-type';
    const HEADER_CONTENT_LENGTH = 'Content-length';
    const HEADER_STATUS = 'Status';

    const STATUS_200 = '200 OK';
    const STATUS_404 = '404 Not Found';
    const STATUS_500 = '500 Internal Server Error';

    protected static $headers;
    protected static $status = '';

    public static function __Init(){
        self::$headers = array();
        self::$status = self::STATUS_200;
        self::setHeader(self::HEADER_CONTENT_TYPE,Mimes::getExtension('html'));
    }

    public static function setJSON(){
        static::setHeader(Response::HEADER_CONTENT_TYPE,Mimes::getType('json'));
    }

    public static function setHeader($param,$value){
        self::$headers[$param]=$value;
    }


    public static function outputHeaders(){
        foreach (self::$headers as $key=>$value){
            header($key.': '.$value);
        }
    }

    public static function setStatus($status){
        self::$status = $status;
    }

    public static function outputStatus(){
        header($_SERVER['SERVER_PROTOCOL'].' '.self::$status);
    }

    public static function Output($data){
        self::outputStatus();
        self::outputHeaders();
        self::saveCache($data);
        echo $data;
    }

    protected static function saveCache($data){
        if (self::$status == self::STATUS_200) {
            HtmlCache::save($data);
        }
    }


    public static function Redirect($url,$code=self::REDIRECT_TEMP, $die=true){
        if (Config::get('DEBUG_REDIRECT')){
            $data =  '<h1>Redirection Intercepted</h1>';
            $data.= '<p>Redirecting to <a href="'.$url.'">'.$url.'</a></p>';
            self::Output($data);
        } else {
            http_response_code($code);
            header('Location: '.$url);
        }
        if ($die == true){
            die();
        }
    }
}