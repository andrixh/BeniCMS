<?php
class DSCache {

    protected static $cache = [];

    protected $keys = [];

    public function add($key){
        if ($key) {
            if (!array_key_exists($key,static::$cache)) {
                $this->keys[$key] = '';
            }
        }
    }

    public function load(){
        //_d($this->cache,'cache');
        if (count($this->keys) > 0){
            $cl = strtoupper(Languages::$active->code);
            $ml = strtoupper(Languages::$main->code);
            $query = Query::Select('mlstrings')->fields('strID',$cl,$ml)->in('strID',array_keys($this->keys));
            $dss = DB::get($query,DB::ASSOC);
            foreach($dss as $ds) {
                $this->keys[$ds['strID']] = $ds[$cl] ? $ds[$cl] : $ds[$ml];
                static::$cache[$ds['strID']] = $this->keys[$ds['strID']];
            }
        }
    }

    public function get($strID){
        $result = '';
        if ($strID) {
            if (array_key_exists($strID,static::$cache)){
                $result = static::$cache[$strID];
            } else if (array_key_exists($strID,$this->keys)) {
                $result = $this->keys[$strID];
            }
        }
        return $result;
    }
}