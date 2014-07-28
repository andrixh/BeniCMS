<?php
namespace Decoder;

use \DB;
use \Query;

class Content extends \Decoder{
    public function decode(){
        _g('ContentList Decode');
        $result = [];
        _d($this->content,'content');
        _d($this->options,'options');
        if ($this->content) {
            $items = json_decode($this->content);
        } else {
            $items = [];
        }

        _d($items,'items');
        if (!is_array($items)) {
            $this->options->content_single = true;
            $items = [$items];
        }

        $contentGroups = array();
        _g('creating content Groups');
        foreach ($items as $item){
            if (!array_key_exists($item->typeID,$contentGroups)){
                $contentGroups[$item->typeID] = [];
            }
            $contentGroups[$item->typeID][] = $item->ID;
        }
        _d($contentGroups,'contentGroups');
        _u();

        _g('fetching from database');
        foreach ($contentGroups as $type=>&$ids) {
            _d($ids,$type);
            $query = Query::Select('contents_'.$type)->in('ID',$ids);
            _d($query,'query');
            $ids = DB::get($query,DB::OBJECT);
            _d($ids,'records');
            if ($ids) {
                foreach ($ids as &$row){
                    _d($row,'row');
                    $row->typeID = $type;
                }
                unset ($row);
            }

        }

        unset($ids);
        _d($contentGroups,'contentgroups');
        _u();

        foreach ($items as $item) {
            $result[] = array_shift($contentGroups[$item->typeID]);
        }

        _g('decoding items');
        _d($result, 'items to be decoded');
        foreach ($result as &$resultItem){
            $typeID = $resultItem->typeID;
            $contentDecoder = new \FieldDecoder(\ContentTypes::get($resultItem->typeID)->scheme,$resultItem);
            $resultItem = $contentDecoder->decode();
            $resultItem['TypeID'] = $typeID;
            unset($contentDecoder);
            _d($resultItem);
        }


        _d($result, 'items decoded');
        unset($resultItem);
        _u();


        if ($this->options->content_single){
            if (count($result) > 0){
                $result = $result[0];
            } else {
                $result = false;
            }
        }
        _d($result,'result');
        _u();
        return $result;
    }
}