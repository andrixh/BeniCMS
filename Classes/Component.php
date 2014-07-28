<?php

class Component
{
    protected $type;
    protected $custom = false;

    public $content;
    protected $context;

    public function __construct($componentID = null, $context=[]) {
        $classNameParts = explode('\\',get_called_class());
        $this->type = array_pop($classNameParts);
        $this->context = $context;
        $this->content = new stdClass();


        if (is_string($componentID) || is_int($componentID)) {
            $query = Query::Select('components_' . lcfirst($this->type));
            if (is_string($componentID)){
                $query->eq('componentID', $componentID);
            } else {
                $query->eq('ID', $componentID);
            }
            $this->content = DB::row($query, DB::OBJECT);
            $componentID = $this->content->componentID;

            unset($this->content->ID);
            unset($this->content->componentID);
            unset($this->content->useCount);
            unset($this->content->type);

            $this->content->_parent = $this->context;

            $templateFile = '/Component/' . ucfirst($this->type) . '_' . $componentID . '.twig';
            $this->custom = file_exists($_SERVER['DOCUMENT_ROOT'] . '/ContentTemplates'.$templateFile)?$templateFile:false;

        } else if (is_object($componentID)) {
            $this->content = $componentID;
        }
        $this->populate();
    }


    protected function populate() {
        if ($this->content){
            $contentDecoder = new FieldDecoder(ComponentTypes::get($this->type)->scheme, $this->content);
            $this->content = $contentDecoder->decode();
        }
    }

    public function prepare() {

    }

    public function output() {
        $renderer = new Renderer();
        _d(get_called_class(),'rendering compoent' );
        if ($this->custom) {
            $result = $renderer->render($this->custom, $this->content);
        } else {
            $result = $renderer->render('/Component/' . ucfirst($this->type). '.twig', $this->content);
        }
        _d($result,'rendering result');
        return $result;
    }
}