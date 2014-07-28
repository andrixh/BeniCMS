<?php
class ComponentType {

    public $id;
    public $scheme;

    public function __construct($record){
        $this->id = $record['typeID'];
        $this->scheme = json_decode($record['scheme']);
    }

    public function getRecordFields(){
        $result = array();
        foreach ($this->scheme as $field){
            $result[] = $field->name;
        }
        return $result;
    }
}