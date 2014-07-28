<?php
class Request{
    protected static $instance;
    public static function __Init(){
        static::$instance = new static();
    }

    public static function getInstance(){
        return static::$instance;
    }

    public $path = [];
    public $queryString = '';
    public $fragment = '';

    public $cacheKey = '';

    /**
     *
     */
    public function __construct()
    {
        _gc('Request Construct');
        $serverName = $_SERVER['SERVER_NAME'];
        $fulluri = $_SERVER['REQUEST_URI'];
        if (strpos($fulluri, $serverName) !== false) {
            $fulluriParts = explode($serverName, $fulluri);
            $fulluri = $fulluriParts[1];
        }
        $fulluriParts = explode('?', $fulluri);
        $fulluri = $fulluriParts[0];
        if (count($fulluriParts) > 1) {
            $queryParts = explode('#',$fulluriParts[1]);
            $this->queryString = $queryParts[0];
            if (count($queryParts) > 1) {
                $this->fragment = $queryParts[1];
            }
        }

        unset ($fulluriParts);

        $uriParts = explode('/', $fulluri);
        _d($uriParts,'uriParts - just exploded');

        if (count($uriParts) > 0 && $uriParts[0] == '') {
            array_shift($uriParts);
            _d($uriParts,'uriParts - array_shift');
        }
        if (count($uriParts) > 0 && $uriParts[count($uriParts) - 1] == '') {
            array_pop($uriParts);
            _d($uriParts,'uriParts - array_pop');
        }


        //detect if empty request and redirect to home page

        $possibleLang = '';
        $possiblePage = '';
        $error = false;

        $partsCount = count($uriParts);
        if ($partsCount == 1) {
            if (strlen($uriParts[0]) == 2) { //could be language and then main page
                $possibleLang = $uriParts[0];
            } else if (strlen($uriParts[0]) > 2) { //could be page with main language
                $possiblePage = $uriParts[0];
            }
        } else if ($partsCount > 1) {
            if (strlen($uriParts[0]) == 2 && strlen($uriParts[1])>2) { //could be lang + pageID
                $possibleLang = $uriParts[0];
                $possiblePage = $uriParts[1];
            } else if (strlen($uriParts[0]) > 2) { //could be main lang + page
                $possiblePage = $uriParts[0];
            }
        }
        _d($uriParts,'uriParts - after guessing');

        _d($possiblePage,'possible Page');
        _d($possibleLang,'possible Lang');

        _d($uriParts,'uriParts');
        $cacheKey = implode('/',$uriParts).(($this->queryString!='')?('?'.$this->queryString):'');
        _d($cacheKey,'cacheKey');
        $this->cacheKey = md5($cacheKey);
        _d($this->cacheKey, 'md5 CacheKey');
        HtmlCache::load($this->cacheKey);


        if ($possibleLang != '') {
            array_shift($uriParts);
        }

        if ($possiblePage != '') {
            array_shift($uriParts);
        }

        $this->path = $uriParts;

        if ($possibleLang == '') {
            _d('setting main language as active');
            Languages::setActive(Languages::$main->code);
        } else {
            if (Languages::is_valid($possibleLang)) {
                _d($possibleLang, 'language is valid');
                Languages::setActive($possibleLang);
            } else {
                _d('error language is invalid');
                $error = true;
            }
        }

        if (!$error) {
            if ($possiblePage == '') {
                Pages::setCurrent(Pages::getMainPage());
            } else {
                if (($possibleLang == '' && Pages::isSystemPage($possiblePage)) || Pages::exists($possiblePage)) {
                    Pages::setCurrent($possiblePage);
                } else {
                    $error = true;
                }
            }
        }

        if ($error) {
            Pages::setCurrent('http404');
        }

        _u();
    }
}