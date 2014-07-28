<?php
class Language {
    public $code;
    public $locale;
    public $name;
    public $flag;

    public function __construct($langID, $langData){
        $this->code = strtolower($langID);
        $this->name = $langData['name'];
        $this->flag = $langData['flag'];
        $this->locale = $langData['locale'];
    }
}