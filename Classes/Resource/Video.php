<?php
namespace Resource;

use DB;
use Query;

class Video implements ResourceInterface{

    public $physicalName;
    public $videoID;
    public $service;

    public function __construct($data){
        if (is_object($data)){
            $this->physicalName = $data->physicalName;
            $this->service = $data->service;
            $this->videoID = $data->videoID;
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

    public function getThumbnail(){
        $record = DB::row(Query::Select('videos')->eq('physicalName',$this->physicalName)->limit(1));
        _d($record,'Video Thumbnail Record');
        $thumb = json_decode($record->thumbnail);
        $img = new Image($thumb[0]);
        _d($img,'Image Extracted');
        return $img ;
    }
}