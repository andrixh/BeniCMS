<?php
class Pages{
    protected static $systemPages = [
        "http404" => "http404",
        "Images" => "images",
        "getFile" => "getFile",
        "Assets"=>"missingAsset"
    ];

    protected static $systemPages_dev = [
        'phpInfo'=>'phpinfo'
    ];

    //private static $pagesRecord;

    protected static $pageData = [];
    protected static $main;
    protected static $current;

    private static $pagesLoaded = false;
    private static $titlesLoaded = false;

    private static $CacheKey = 'PageStruct';

    public static function __Init(){
        _g('Pages::Init');
        if (Env::isDev()){
            static::$systemPages = array_merge(static::$systemPages, static::$systemPages_dev);
        }
        _u();
    }

    private static function loadTitles(){
        if (static::$titlesLoaded) {
            return;
        }
        $titles = new DSCache();
        foreach (static::$pageData as $pd) {

            $titles->add($pd->title);
            $titles->add($pd->menuTitle);

        }
        $titles->load();

        foreach (static::$pageData as $page) {
            $page->title = $titles->get($page->title);
            $page->menuTitle = $titles->get($page->menuTitle);
        }

        //static::$pagesRecord = null;
        static::$titlesLoaded = true;
    }


    private static function loadPages(){

        if (static::$pagesLoaded){
            return;
        }

        if (StructureCache::has(static::$CacheKey)){
            list(static::$pageData,static::$main) = static::readFromCache();
        } else {
            list(static::$pageData,static::$main) = static::readFromDb();
            static::writeToCache(static::$pageData, static::$main);
        }

        static::$pagesLoaded = true;
    }

    private static function readFromDb(){

        $pageData = [];
        $main = null;

        $pagesRecord = DB::get(Query::Select('pages')->eq('active',1)->asc('rank'),DB::ASSOC);
        foreach ($pagesRecord as $pd) {
            $newPage = new PageDef($pd);
            $pageData[$newPage->id] = $newPage;
        }
        _d($pageData,'pageData');

        foreach ($pageData as $id=>$page) {
            if ($page->parent){
                $par = $page->parent;
                $page->parent = $pageData[$par];
                $page->parent->addChild($page);
            }
            if ($page->rep) {
                $pageData[$page->rep]->addRep($page->id);
            }
            if ($page->main) {
                $main = $id;
            }
        }

        return [$pageData,$main];
    }

    private static function readFromCache(){
        $data = StructureCache::get(static::$CacheKey);
        return [$data['pageData'],$data['main']];
    }

    private static function writeToCache($pageData,$main){
        StructureCache::set(static::$CacheKey,['pageData'=>$pageData,'main'=>$main]);
    }

    public static function getAll(){
        static::loadPages();
        return static::$pageData;
    }

    public static function exists($id){
        static::loadPages();
        return array_key_exists($id,static::$pageData);
    }

    public static function isSystemPage($id){
        return array_key_exists($id,static::$systemPages);
    }

    public static function get($id){
        static::loadPages();
        return static::$pageData[$id];
    }

    public static function getMainPage(){
        static::loadPages();
        return static::$main;
    }

    public static function setCurrent($id){
        if (array_key_exists($id,static::$systemPages)){
            $systemPageClass = "\\Page\\".ucfirst(static::$systemPages[$id]);
            static::$current = new $systemPageClass();
        } else {
            static::loadPages();
            static::loadTitles();
            $cPageDef = static::get($id);
            $pageClass = "\\Page\\".ucfirst($cPageDef->type->id);
            static::$current = new $pageClass($cPageDef);
        }
    }

    public static function getCurrent(){
        return static::$current;
    }
}