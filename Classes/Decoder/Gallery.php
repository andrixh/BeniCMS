<?php
namespace Decoder;

use Resource\Image;
use Resource\Video;

class Gallery extends \Decoder{
    public function decode(){
        _d($this->content,'content to decode');
        $result = array();
        if (!$this->content){
            return false;
        }
        $items = json_decode($this->content);
        foreach ($items as $item){
            if ($item->resourceType == 'image'){
                $result[] = new Image($item);
            } else if ($item->resourceType == 'video') {
                $result[] = new Video($item);
            }
        }
        _d($this->options);
        if ($this->options->gallery_single){
            if (count($result) > 0){
                $result = $result[0];
            } else {
                $result = false;
            }
        }
        return $result;
    }
}