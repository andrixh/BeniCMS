<?php
class ComponentTypes {
    public static $types = [];

    private static $CacheKey = 'ComponentTypes';


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

        $typeRecords = DB::get(Query::Select('componenttypes')->fields('typeID','scheme'),DB::ASSOC);
        foreach ($typeRecords as $typeRecord) {
            $typeDef = new ComponentType($typeRecord);
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

    /**
     * @param $type
     * @return ComponentType
     */
    public static function get($type) {
        return static::$types[lcfirst($type)];
    }
}