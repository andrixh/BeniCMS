<?php
namespace Page;

use Response;
use Request;
use Config;
use DB;
use Mimes;


class GetFile extends \SystemPage {
    public function prepare(){
        $this->validateRequest();

    }

    public $fileRow = null;


    public function output(){
        _d($this,'beforeoutput');

        $fileSize = $this->fileRow->size;


        Response::setHeader(Response::HEADER_CONTENT_LENGTH,$fileSize);
        Response::setHeader("Cache-Control",'public');
        Response::setHeader("Content-Description","File Transfer");
        Response::setHeader("Content-Disposition:",'attachment; filename='.$this->fileRow->fileName.'.'.$this->fileRow->extension);
        Response::setHeader(Response::HEADER_CONTENT_TYPE, Mimes::getType($this->fileRow->extension));
        Response::setHeader("content-Transfer-Encoding","binary");
        Response::Output(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/Files/'.$this->fileRow->physicalName));

    }

    protected function validateRequest(){
        $req = Request::getInstance()->path;

        if (count($req) != 1){
            $this->fail('not enough parameter parts');
        }
        _d($req);

        $filePName = $req[0];

        $this->fileRow = DB::row(\Query::Select('files')->eq('physicalName',$filePName));
        if (!$this->fileRow){
            $this->fail('not found in database');
        }

        if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/Files/'.$filePName)){
            $this->fail('not found in disk');
        }
    }



    protected function fail($reason,$label=''){
        _d($reason,$label);
        Response::setStatus(Response::STATUS_404);
        Response::Output('404 - File not found');
        die();
    }
}