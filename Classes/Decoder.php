<?php
abstract class Decoder{
    protected $options = array();
    protected $content;

    public function __construct($content,$options = array()){
        $this->options = new stdClass();
        $this->content = $content;
        $this->options = $options;
    }

    public function decode(){
        return $this->content;
    }
}