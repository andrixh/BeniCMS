<?php
namespace Decoder;

use Decoder\Image;

class Mlhtml extends \Decoder{
    public function decode(){
        _g('Mlhtml decoder');
        _d($this->content);
        $result = array();

        $pq = \phpQuery::newDocument($this->content);
        _d($pq->htmlOuter());
        $fileLinks = pq($pq)->find('a.file');
        foreach ($fileLinks as $fileLink){
            pq($fileLink)->attr('href','/getFile/'.pq($fileLink)->attr('href'));
        }
        $elements = pq($pq)->children();
        _g('decoding elements');
        foreach ($elements as $element){;
            _d(pq($element)->html());
            if (pq($element)->is('[data-component]')){
                $componentData = json_decode(pq($element)->attr('data-component'));
                _d($componentData);
                $componentClass = '\\Component\\'.ucfirst($componentData->typeID);
                _d($componentClass,'class for component');
                $component = new $componentClass($componentData->componentID);
                $component->prepare();
                $result[] = $component->output();
                _d(json_decode(pq($element)->attr('data-component')),'component found');
            } else if (pq($element)->is('[data-image]')){
                $img = json_decode(pq($element)->attr('data-image'));
                $imgObject = new Image($img);
                $result[] = $imgObject->decode();
            } else if (pq($element)->is('[data-video]')){
                $vid = json_decode(pq($element)->attr('data-video'));
                _d($vid,'video data');
                $videoObject = new Video($vid);
                $result[] = $videoObject->decode();
            } else {
                if (count($result == 0) || is_object($result[count($result)-1])){
                    $result[] = '';
                }
                $result[count($result)-1] .= pq($element)->htmlOuter();

            }
        }
        _u();
        _d($result,'result');
        _u();
        return implode('',$result);
    }
}