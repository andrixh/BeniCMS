<?php
namespace Decoder;
use Query;
use DB;

class Video Extends \Decoder {
    public $physicalName;
    public $videoID;
    public $service;
    //public $thumbnail;
    //public $thumbnailType;
    public $width=320;
    public $height=240;
    public $autoPlay = false;
    public $loop = false;
    public $controls = true;
    public $color = '';

    public $align = 'C';
    public $caption = false;

    protected $record = null;
    protected $description = '';


    public function __construct($content, $options=[]){
        _d($content,'Decoding Video');
        $this->physicalName = $content->physicalName;
        $this->videoID = $content->videoID;
        $this->service = $content->service;


        $this->width = property_exists($content,'width')?$content->width:(array_key_exists('width',$options)?$options['width']:$this->width);
        $this->height = property_exists($content,'height')?$content->height:(array_key_exists('height',$options)?$options['height']:$this->height);

        $this->autoPlay = property_exists($content,'autoPlay')?$content->autoPlay:(array_key_exists('autoPlay',$options)?$options['autoPlay']:$this->autoPlay);
        $this->loop = property_exists($content,'loop')?$content->loop:(array_key_exists('loop',$options)?$options['loop']:$this->loop);
        $this->controls = property_exists($content,'controls')?$content->controls:(array_key_exists('controls',$options)?$options['controls']:$this->controls);
        $this->color = property_exists($content,'color')?$content->color:(array_key_exists('color',$options)?$options['color']:$this->color);

        $this->align = property_exists($content,'align')?$content->align:(array_key_exists('align',$options)?$options['align']:$this->align);
        $this->caption = property_exists($content,'caption')?$content->caption:(array_key_exists('caption',$options)?$options['caption']:$this->caption);
    }


    public function decode(){
        $serviceDecoderClass = "\\Decoder\\Video\\".ucfirst($this->service);
        $decoder = new $serviceDecoderClass($this);
        return $decoder->getResult();
    }

}