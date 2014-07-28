<?php
namespace Decoder;

use Resource\File;

class Files extends \Decoder{
    public function decode(){
        _d($this->content,'files content to decode');
        $result = [];
        if (!$this->content){
            return false;
        }
        $items = json_decode($this->content);
        foreach ($items as $item){
            $result[] = new File($item);
        }
        _d($this->options);
        if ($this->options->files_single){
            if (count($result) > 0){
                $result = $result[0];
            } else {
                $result = false;
            }
        }
        return $result;
    }
}