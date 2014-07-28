<?php
namespace Resource;

use DB;

class File implements ResourceInterface{

    public $identifier;
    public $extension;
    public $filename;
    public $size;

    public function __construct($data){
        if (is_object($data)){
            $this->identifier = $data->physicalName;
            $this->extension = $data->extension;
        }
        $record = DB::row(\Query::Select('files')->eq('physicalName',$data->physicalName));
        if ($record){
            $this->filename = $record->fileName;
            $this->size = $record->size;
        }
    }

    public function getIdentifier()
    {
        return $this->physicalName;
    }

    public function getType()
    {
        return $this->extension;
    }
}