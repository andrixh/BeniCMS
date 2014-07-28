<?php
namespace Decoder;

class Date extends \Decoder{
    public function decode(){
        return new \DateTime($this->content);
    }
}