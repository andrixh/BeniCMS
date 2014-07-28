<?php
namespace Decoder\Video;

class Youtube{
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
        $result.= 'src="//www.youtube.com/embed/'.$this->item->videoID.'?rel=0&modestbranding=1&iv_load_policy=0';

        if ($this->item->autoPlay) {
            $result.='autoPlay=1&';
        }
        if ($this->item->loop) {
            $result.='loop=1&';
        }
        if (!$this->item->controls) {
            $result.='controls=0&showinfo=0';
        }
        if ($this->item->color){
            $result.='theme=light&';
        }
        $result.= '" webkitallowfullscreen mozallowfullscreen allowfullscreen frameborder="0"></iframe>';
        return $result;
    }

}