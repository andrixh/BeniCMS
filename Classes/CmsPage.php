<?php
class CmsPage extends Page {

    public $pageDef;

    public $id;
    public $title;
    public $menuTitle;
    public $content;
    public $lang;

    /**
     * @param $pageDef PageDef
     */
    public function __construct($pageDef){
        _d($pageDef,'created Page');
        $this->pageDef = $pageDef;
        if (!$this->pageDef->cache) {
            HtmlCache::off();
        }
        $this->populate();
    }

    protected function populate(){
        $this->id = $this->pageDef->id;
        $this->title = $this->pageDef->title;
        $this->menuTitle = $this->pageDef->menuTitle;
        $this->lang = Languages::$active->code;
        $this->content = DB::row(Query::Select('pages_'.strtolower($this->pageDef->type->id))->eq('pageID',$this->id));
        //unset($this->content['ID']);
        //unset($this->content['pageID']);
        $contentDecoder = new FieldDecoder($this->pageDef->type->scheme,$this->content);
        $this->content = $contentDecoder->decode();
    }

    public function prepare(){

    }

    public function output(){
        $renderer = new Renderer();
        $data = $this->content;
        $data['ID']=$this->id;
        $data['Title']=$this->title;
        $data['MenuTitle']=$this->title;
        $data['Lang']=$this->lang;
        Response::Output($renderer->render('/Page/'.ucfirst($this->pageDef->type->id).'.twig',$data));
    }
}