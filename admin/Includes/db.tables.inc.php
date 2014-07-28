<?php
require_once "Lib/DB/DbSchema.php";
require_once "Lib/DB/DbSchemaField.php";

//Field definition :  'fieldname'=>'TYPE(##), [PRIMARY|INDEX|UNIQUE], [AUTO_INCREMENT], [NULL|NOT NULL], [=DEFAULT]', [//Comment]);

$_dbTables = [
    'admins' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'userName' => 'VARCHAR(100)',
        'password' => 'TEXT',
        'fullName' => 'TEXT',
        'email' => 'VARCHAR(200)',
        'active' => 'TINYINT(1)',
        'role' => 'INT(2)'
    ],
    'admins_resetpassword' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'resetCode' => 'VARCHAR(50)',
        'userName' => 'VARCHAR(100)',
        'time' => 'INT(10)',
        'ip' => 'VARCHAR(40)',
        'expired' => 'TINYINT(1)',
        'used' => 'TINYINT(1)'
    ],
    'site' => [
        'name' => 'VARCHAR(50)',
        'hasFavicon' => 'TINYINT(1)',
        'faviconIndex' => 'INT(10)',
        'metaDescription' => 'VARCHAR(50)',
        'metaKeywords' => 'VARCHAR(50)',
        'trackingCode' => 'TEXT'
    ],
    'languages' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'langID' => 'VARCHAR(2)',
        'active' => 'TINYINT(1)',
        'main' => 'TINYINT(1)',
        'rank' => 'TINYINT(1)'
    ],
    'interface' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'strID' => 'VARCHAR(80)',
        'value' => 'VARCHAR(50)'
    ],
    'mlstrings' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'strID' => 'VARCHAR(50), UNIQUE',
        'index' => 'TINYINT(1)',
        'usedTable' => 'VARCHAR(60)',
        'usedID' => 'INT(10)',
    ],
    'files' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'physicalName' => 'VARCHAR(50), UNIQUE',
        'extension' => 'VARCHAR(10)',
        'fileName' => 'VARCHAR(255)',
        'useCount' => 'INT(10)',
        'size' => 'BIGINT(20)'
    ],
    'images' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'physicalName' => 'VARCHAR(50), UNIQUE',
        'type' => 'VARCHAR(3)',
        'label' => 'VARCHAR(255)',
        'description' => 'VARCHAR(50)',
        'useCount' => 'INT(10)',
        'width' => 'INT(10)',
        'height' => 'INT(10)'
    ],
    'videos' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'physicalName' => 'VARCHAR(50), UNIQUE',
        'videoID' => 'VARCHAR(50)',
        'service' => 'VARCHAR(50)',
        'label' => 'VARCHAR(255)',
        'description' => 'VARCHAR(50)',
        'thumbnail' => 'VARCHAR(255)',
        'ownThumbnail' => 'TINYINT(1)',
        'useCount' => 'INT(10)'
    ],
    'selectproviders' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'providerID' => 'VARCHAR(50)',
        'label' => 'VARCHAR(50)',
        'editorUrl' => 'VARCHAR(255)',
        'useCount' => 'INT(10)',
        'options' => 'TEXT'

    ],
    'pages' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'pageID' => 'VARCHAR(255), UNIQUE',
        'type' => 'VARCHAR(255)',
        'main' => 'TINYINT(1)',
        'parent' => 'VARCHAR(255)',
        'rank' => 'INT(10)',
        'rep' => 'VARCHAR(255)',
        'menuGroups' => 'INT(10)',
        'active' => 'TINYINT(1)',
        'link' => 'VARCHAR(255)',
        'track' => 'TINYINT(1)',
        'title' => 'VARCHAR(50)',
        'menuTitle' => 'VARCHAR(50)',
        'cache' => 'TINYINT(1)',
    ],
    'pagetypes' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'typeID' => 'VARCHAR(50), UNIQUE',
        'label' => 'VARCHAR(50)',
        'comment' => 'VARCHAR(255)',
        'icon' => 'VARCHAR(255)',
        'formTemplate' => 'TEXT',
        'scheme' => 'TEXT',
        'useCount' => 'INT(10)',
        'rank' => 'INT(10)',
        'hidden' => 'TINYINT(1)'
    ],
    'contenttypes' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'typeID' => 'VARCHAR(50)',
        'label' => 'VARCHAR(50)',
        'comment' => 'VARCHAR(255)',
        'icon' => 'VARCHAR(255)',
        'viewer' => 'VARCHAR(255)',
        'formTemplate' => 'TEXT',
        'listTemplate' => 'TEXT',
        'scheme' => 'TEXT',
        'useCount' => 'INT(10)',
        'rank' => 'INT(10)',
        'hidden' => 'TINYINT(1)'
    ],
    'componenttypes' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'typeID' => 'VARCHAR(50)',
        'label' => 'VARCHAR(50)',
        'comment' => 'VARCHAR(255)',
        'icon' => 'VARCHAR(255)',
        'formTemplate' => 'TEXT',
        'listTemplate' => 'TEXT',
        'scheme' => 'TEXT',
        'useCount' => 'INT(10)',
        'rank' => 'INT(10)',
        'hidden' => 'TINYINT(1)'
    ],
    'components_' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'componentID' => 'VARCHAR(255), UNIQUE',
        'useCount' => 'INT(10)',
    ],
    'contents_' => [
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'contentID' => 'VARCHAR(255), UNIQUE',
        'useCount' => 'INT(10)',
    ],
    'pages_'=>[
        'ID' => 'INT(10), PRIMARY, AUTO_INCREMENT, NOT NULL',
        'pageID' => 'VARCHAR(255), UNIQUE',
    ]
];

$_dbFieldTypes = [
    'boolean' => 'TINYINT(1), INDEX',
    'string' => 'TEXT',
    'number' => 'FLOAT',
    'date' => 'DATE',
    'select' => 'VARCHAR(255)',
    'page' => 'VARCHAR(255)',
    'mlstring' => 'VARCHAR(50)',
    'mlhtml' => 'VARCHAR(50)',
    'mlgallery' => 'VARCHAR(50)',
    'mlfiles' => 'VARCHAR(50)',
    'gallery' => 'TEXT',
    'files' => 'TEXT',
    'content' => 'TEXT',
    'contentselect' => 'INT(10)',
    'component' => 'INT(10)'
];

require_table('languages');
$tmp_languages = DB::get(Query::Select('languages')->fields('langID')->desc('main')->asc('rank'));
if ($tmp_languages) {
    foreach ($tmp_languages as $lang) {
        $_dbTables['mlstrings'][strtoupper($lang->langID)] = 'TEXT';
    }
}

function require_table($tableName, $definition=''){
    _g('Require Table '.$tableName);
    _d(func_get_args(),'arguments');
    global $_dbTables;
    if ($definition == ''){
        if (strpos($tableName,'pages_') === 0){
            $pageType = str_replace('pages_','',$tableName);
            $contentDef = contentDef(json_decode(DB::val(Query::Select('pagetypes')->eq('typeID',$pageType)->fields('scheme'))));
            $definition = array_merge($_dbTables['pages_'],$contentDef);
        } else if (strpos($tableName,'components_') === 0){
            $pageType = str_replace('components_','',$tableName);
            $contentDef = contentDef(json_decode(DB::val(Query::Select('componenttypes')->eq('typeID',$pageType)->fields('scheme'))));
            $definition = array_merge($_dbTables['components_'],$contentDef);
        } else if (strpos($tableName,'contents_') === 0){
            $pageType = str_replace('contents_','',$tableName);
            $contentDef = contentDef(json_decode(DB::val(Query::Select('contenttypes')->eq('typeID',$pageType)->fields('scheme'))));
            $definition = array_merge($_dbTables['contents_'],$contentDef);
        } else {
            $definition = $_dbTables[$tableName];
        }
    }
    _d($tableName,'tableName');
    _d($definition,'definition');
    DbSchema::Update($tableName,$definition);
    _u();
}

function contentDef($scheme)
{
    global $_dbFieldTypes;
    $result = [];
    foreach ($scheme as $schemeField) {
        $result[$schemeField->name] = $_dbFieldTypes[$schemeField->type];
    }
    return $result;
}