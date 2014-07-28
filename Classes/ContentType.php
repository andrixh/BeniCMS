<?php
class ContentType {

    public $id;
    public $scheme;

    public function __construct($record){
        $this->id = $record['typeID'];
        $this->scheme = json_decode($record['scheme']);
    }
}