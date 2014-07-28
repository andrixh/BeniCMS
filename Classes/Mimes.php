<?php
class Mimes {
    protected static $types = [
        //html and related
        'html'=>'text/html',
        'css'=>'text/css',
        'js'=>'application/javascript',
        'json'=>'application/json',
        //images
        'jpg'=>'image/jpeg',
        'jpeg'=>'image/jpeg',
        'png'=>'image/png',
        'gif'=>'image/gif',
        'svg'=>'image/svg+xml',
        //fonts
        'eot'=>'application/vnd.ms-fontobject',
        'woff'=>'application/x-font-woff',
        'ttf'=>'application/octet-stream',
        'otf'=>'application/octet-stream',
        //documents
        'txt'=>'application/plain',
        'rtf'=>'application/rtf',
        'pdf'=>'application/pdf',
        'doc'=>'application/msword',
        'docx'=>'application/msword',
        'xls'=>'application/excel',
        'xlsx'=>'application/excel',
        'ppt'=>'application/powerpoint',
        'pptx'=>'application/powerpoint',
        'pps'=>'application/powerpoint',
        'ppsx'=>'application/powerpoint',
        'rar'=>'application/x-rar-compressed',
        'zip'=>'application/zip'
    ];

    protected static $default = 'application/octet-stream';

    public static function getType($extension){
        if (array_key_exists(strtolower($extension),static::$types)){
            return static::$types[$extension];
        } else {
            return static::$default;
        }
    }

    public static function getExtension($type){
        return array_search(strtolower($type),static::$types);
    }

    public static function getDefault(){
        return static::$default;
    }
}