<?php

class Site {
    protected static $instance = null;
    public $name = '';
    public $hasFavicon = false;
    public $faviconIndex = 0;
    public $metaDescription = '';
    public $metaKeywords= '';
    public $trackingCode = '';
    public $serverName = '';

    public static function __Init(){
        _d('site::Init');
        if (is_null(self::$instance)){
            self::$instance = new self();
        }
    }

    public static function getInstance(){
        _d(static::$instance,'Site::getInstance');
        return static::$instance;
    }

    public function __construct(){
        $row = DB::row(Query::Select('site'));
        if ($row) {
            $dsCacher = new DSCache();
            $dsCacher->add($row->name);
            $dsCacher->add($row->metaDescription);
            $dsCacher->add($row->metaKeywords);
            $dsCacher->load();

            $this->name = $dsCacher->get($row->name);
            $this->hasFavicon = $row->hasFavicon;
            $this->faviconIndex = $row->faviconIndex;
            $this->metaDescription = $dsCacher->get($row->metaDescription);
            $this->metaKeywords = $dsCacher->get($row->metaKeywords);
            $this->trackingCode = $row->trackingCode;
            $this->serverName = $_SERVER['SERVER_NAME'];
        }
    }
}