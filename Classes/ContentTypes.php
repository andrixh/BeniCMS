<?php
class ContentTypes {
    public static $types = [];

    private static $CacheKey = 'ContentTypes';

    public static function __Init(){
        if (StructureCache::has(static::$CacheKey)){
            static::$types = static::readFromCache();
        } else {
            static::$types = static::readFromDb();
            static::writeToCache(static::$types);
        }
    }

    private static function readFromDb(){
        $result = [];

        $typeRecords = DB::get(Query::Select('contenttypes'),DB::ASSOC);
        foreach ($typeRecords as $typeRecord) {
            $typeDef = new ContentType($typeRecord);
            $result[$typeDef->id] = $typeDef;
        }

        return $result;
    }

    private static function readFromCache(){
        return StructureCache::get(static::$CacheKey);
    }

    private static function writeToCache($types){
        StructureCache::set(static::$CacheKey,$types);
    }

    public static function get($type) {
        _d($type,'contentTypes requested');
        return static::$types[$type];
    }
}