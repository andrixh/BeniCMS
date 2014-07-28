<?php
class Languages {
    public static $all = [];
    /**
     * @var Language
     */
    public static $active;
    public static $main;
    public static $count;

    private static $CacheKey = 'Languages';

    //private static $languageData;

    public static function __Init(){

        if (StructureCache::has(static::$CacheKey)){
            list(static::$all,static::$main) = static::readFromCache();
        } else {
            list(static::$all,static::$main) = static::readFromDb();
            static::writeToCache(static::$all, static::$main);
        }

        static::$count = count(static::$all);

        if (count(static::$all) > 0){
            if (isset($_SESSION) && isset($_SESSION['lang'])) {
                static::setActive($_SESSION['lang']);
            } else {
                static::setActive(static::$main->code);
            }
        }

    }

    private static function readFromDb(){
        $languageData = include($_SERVER['DOCUMENT_ROOT'].'/Config/Languages/languages.php');

        $all = [];
        $main = null;

        $langs = DB::get(Query::Select('languages')->eq('active',1)->asc('rank'),DB::ASSOC);

        foreach ($langs as $lang) {
            $all[strtolower($lang['langID'])] = new Language($lang['langID'], $languageData[strtoupper($lang['langID'])]);
            if ($lang['main']) {
                $main = $all[strtolower($lang['langID'])];
            }
        }

        return [$all,$main];

    }

    private static function writeToCache($all,$main){
        StructureCache::set(static::$CacheKey,['all'=>$all,'main'=>$main]);
    }

    private static function readFromCache(){
        $data = StructureCache::get(static::$CacheKey);
        return [$data['all'],$data['main']];
    }

    public static function is_valid($code){
        return is_string($code) && array_key_exists(strtolower($code), static::$all);
    }

    public static  function setActive($code){
        if (static::is_valid($code)){
            $locale = static::$all[strtolower($code)]->locale;
            _d($locale,'locale to set');
            $localeResult = setlocale(LC_TIME,$locale);
            _d($localeResult, 'LocaleResult');
            static::$active = static::$all[strtolower($code)];
            if (isset($_SESSION)){
                $_SESSION['lang'] = static::$active->code;
            }
        }
    }
}