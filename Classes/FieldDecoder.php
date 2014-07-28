<?php
class FieldDecoder {
    protected $scheme;
    protected $content;

    protected static $mlFields = array(
        'mlstring'=>'string',
        'mlhtml'=>'mlhtml',
        'mlgallery'=>'gallery',
        'mlfiles'=>'files'
    );

    public function __construct($scheme,$content){
        $this->scheme = unserialize(serialize((array)($scheme)));
        $this->content = $content;

    }

    public function decode(){
        //_gc('FieldDecoder Decode()');
        //_d($this,'fieldDecoder');
        $result = array();
        $dss = new DSCache();
        foreach ($this->scheme as $field){

            //_d($field,'field');
            //_d($this->content,'content');
            //_d($field->name,'field name');
            if (array_key_exists($field->type,static::$mlFields)){
                $dss->add($this->content->{$field->name});
            }
        }
        $dss->load();
        foreach ($this->scheme as &$field){

            if (array_key_exists($field->type,static::$mlFields)){
                $this->content->{$field->name} = $dss->get($this->content->{$field->name});
                $field->type = static::$mlFields[$field->type];
            }
        }

        unset($field);

        foreach ($this->scheme as $field){
            $fieldType = $field->type;
            $fieldName = $field->name;
            $fieldOptions = $field->options;

            $decoderClass = "\\Decoder\\".ucfirst($fieldType);
            $content = $this->content->{$fieldName};
            $decoder = new $decoderClass($content,$fieldOptions);

            $result[$fieldName] = $decoder->decode();
        }
        //_d($result,'decoding result');
        //_u();
        return $result;
    }
}