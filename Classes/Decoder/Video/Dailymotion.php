<?php
namespace Decoder\Video;

class Dailymotion{
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
        $result.= 'src="http://www.dailymotion.com/embed/video/'.$this->item->videoID.'?rel=0&modestbranding=1';

        if ($this->item->autoPlay) {
            $result.='autoPlay=1&';
        }
        if ($this->item->loop) {
            $result.='loop=1&';
        }
        if (!$this->item->controls) {
            $result.='controls=0&';
        }
        $result.= '"></iframe>';
        return $result;
    }

}