<?php
namespace Decoder;

use DB;
use Query;

class Contentselect extends Content{
    public function __construct($content, $options = []){
        _g('ContentSelect Decoder __construct');
        _d(func_get_args());
        parent::__construct($content,$options);
        $this->options->content_single = true;
        _u();
    }

    public function decode(){
        _g('ContentSelect decode()');
        _d($this->content,'content');
        _d($this->options,'options');
        $type = $this->options->contentTypes;
        $record = DB::row(Query::Select('contents_'.$type)->id($this->content));

        $record->typeID = $type;
        $scheme = \ContentTypes::get($record->typeID)->scheme;
        _d($scheme,'scheme');
        $contentDecoder = new \FieldDecoder($scheme,$record);
        $result = $contentDecoder->decode();
        _d($result,'decoded result');
        _u();
        return $result;
    }
}