<?php
class PageDef {
    public $id, $type, $parent, $main, $rank, $rep, $menuGroups, $link, $track, $title, $menuTitle, $cache;

    public $children = array();
    public $reps = array();

    public function __construct($record){
        $this->id = $record['pageID'];
        $this->type = PageTypes::get($record['type']);
        $this->parent = $record['parent'];
        $this->main = (bool)$record['main'];
        $this->rank = $record['rank'];
        $this->rep = $record['rep'];
        $this->menuGroups = [(bool)($record['menuGroups'] & 1),(bool)($record['menuGroups'] & 2),(bool)($record['menuGroups'] & 4)];
        $this->link = $record['link'];
        $this->track = $record['track'];
        $this->title = $record['title'];
        $this->menuTitle = $record['menuTitle'];
        $this->cache = (bool)$record['cache'];

    }

    public function addChild($child){
        $this->children[] = $child;
    }

    public function addRep($rep){
        $this->reps[] = $rep;
    }


}