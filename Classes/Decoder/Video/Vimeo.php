<?php
namespace Decoder\Video;

class Vimeo{
    /**
     * @var \Decoder\Video
     */
    protected $item;

    /**
     * @param $item \Decoder\Video
     */
    public function __construct($item){
        $this->item = $item;
    }

    public function getResult(){
        $result = '<iframe type="text/html" ';
        $result.= 'width="'.$this->item->width.'" ';
        $result.= 'height="'.$this->item->height.'" ';
        $result.= 'src="//player.vimeo.com/video/'.$this->item->videoID.'?';

        if ($this->item->autoPlay) {
            $result.='autoPlay=1&';
        }
        if ($this->item->loop) {
            $result.='loop=1&';
        }
        if (!$this->item->controls) {
            $result.='title=0&badge=0&byline=0&portrait=0';
        }
        if ($this->item->color) {
            $result.='color='.$this->item->color;
        }
        $result.= '" webkitallowfullscreen mozallowfullscreen allowfullscreen frameborder="0"></iframe>';
        return $result;
    }

}