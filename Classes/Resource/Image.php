<?php
namespace Resource;

class Image implements ResourceInterface{

    public $physicalName;
    public $type;

    public function __construct($data){
        if (is_object($data)){
            $this->physicalName = $data->physicalName;
            $this->type = $data->type;
        }
    }

    public function getIdentifier()
    {
        return $this->physicalName;
    }

    public function getType()
    {
        return $this->type;
    }
}