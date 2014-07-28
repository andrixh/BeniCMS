<?php
namespace Decoder;
use Query;
use DB;

class Image Extends \Decoder {
    public $physicalName;
    public $type;
    public $width = 0;
    public $height = 0;
    public $autoWidth = false;
    public $autoHeight = false;
    public $align = 'C'; //L R C
    public $scaleMode = 'S';
    public $border = false;
    public $caption = '';
    public $quality;
    public $link;

    protected $srcOnly = false;
    protected $record = null;
    protected $description = '';
    protected $altText = '';

    public function __construct($content, $options=[]){

        $this->physicalName = $content->physicalName;
        $this->type = $content->type;
        $this->width = property_exists($content,'width')?$content->width:(array_key_exists('width',$options)?$options['width']:$this->width);
        $this->height = property_exists($content,'height')?$content->height:(array_key_exists('height',$options)?$options['height']:$this->height);
        $this->autoWidth = property_exists($content,'autoWidth')?$content->autoWidth:(array_key_exists('autoWidth',$options)?$options['autoWidth']:$this->autoWidth);
        $this->autoHeight = property_exists($content,'autoHeight')?$content->autoHeight:(array_key_exists('autoHeight',$options)?$options['autoHeight']:$this->autoHeight);
        $this->align = property_exists($content,'align')?$content->align:(array_key_exists('align',$options)?$options['align']:$this->align);
        $this->scaleMode = property_exists($content,'scaleMode')?$content->scaleMode:(array_key_exists('scaleMode',$options)?$options['scaleMode']:$this->scaleMode);
        $this->border = property_exists($content,'border')?$content->border:(array_key_exists('border',$options)?$options['border']:$this->border);
        $this->caption = property_exists($content,'caption')?$content->caption:(array_key_exists('caption',$options)?$options['caption']:$this->caption);
        $this->link = property_exists($content,'link')?$content->link:(array_key_exists('link',$options)?$options['link']:$this->link);
        $this->quality = property_exists($content,'quality')?$content->quality:(array_key_exists('quality',$options)?$options['quality']:\Config::get('IMAGE_STANDARD_QUALITY'));
        $this->srcOnly = property_exists($content,'srcOnly')?$content->scrOnly:(array_key_exists('srcOnly',$options)?$options['srcOnly']:$this->srcOnly);
    }

    protected function getImageRecord(){
        $query = \Query::Select('images')->eq('physicalName',$this->physicalName);
        $this->record = DB::row($query);
    }

    protected function getDescription(){
        if ($this->record->description){
            $dscache = new \DSCache();
            $dscache->add($this->record->description);
            $dscache->load();
            $this->description = $dscache->get($this->record->description);
            $this->altText = $this->description;
        } else {
            $this->altText = $this->record->label;
            $this->caption = false;
        }
    }

    public function decode(){
        $this->getImageRecord();
        $this->getDescription();

        $alignClasses =[
            'C'=>'',
            'L'=>' left ',
            'R'=>' right ',
        ];

        $wrapIn = '';
        $wrapOut = '';

        $label = '';

        if ($this->autoWidth){
            $this->width = '0';
        }


        if ($this->autoHeight){
            $this->height = '0';
        }

        $imgSrc = '/Images/Resized/';
        $imgSrc.= $this->physicalName;
        $imgSrc.='_'.$this->width;
        $imgSrc.='_'.$this->height;
        $imgSrc.='_'.$this->scaleMode;
        $imgSrc.='_'.$this->quality;
        $imgSrc.='.'.$this->type;

        if ($this->srcOnly){
            $imgTag = $imgSrc;
        } else {
            $imgTag = '<img src="'.$imgSrc.'" alt="'.htmlentities($this->altText).'" ';

            if (!$this->caption && !$this->border) {
                $imgTag.='class="'.$alignClasses[$this->align].'"';
            }
            $imgTag.='/>';
        }


        if ($this->caption || $this->border){
            $wrapIn = '<figure class="';
            $wrapIn.=$alignClasses[$this->align];
            $wrapIn.=$this->border?' border':'';
            $wrapIn.='">';
            if ($this->caption){
                $label = '<figcaption><p>'.nl2br(htmlentities($this->description)).'</p></figacption>';
            }

            $wrapOut='</figure>';
        }

        return $wrapIn.$imgTag.$label.$wrapOut;

    }

}