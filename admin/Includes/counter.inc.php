<?php
/*
 * Counter keeps track of resource usage
 */

 
//array that holds resource types and their respective database tables
$_counterTypes = [
	'image'=>['table'=>'images', 'field'=>'physicalname'],
	'video'=>['table'=>'videos', 'field'=>'physicalname'],
	'file'=>['table'=>'files', 'field'=>'physicalname'],
	'provider'=>['table'=>'selectproviders', 'field'=>'providerID'],
	'pagetype'=>['table'=>'pagetypes', 'field'=>'typeID'],
	'componenttype'=>['table'=>'componenttypes', 'field'=>'typeID'],
	'contenttype'=>['table'=>'contenttypes', 'field'=>'typeID'],
];

//Insert extra component tables

function insertExtraComponentTypes(){
	global $db;
	global $_counterTypes;
	$componentTypes = DB::get(Query::Select('componenttypes')->fields('typeID'));
	if ($componentTypes){
		foreach ($componentTypes as $componentType){
			$_counterTypes['components_'.$componentType->typeID]=array('table'=>'components_'.$componentType->typeID, 'field'=>'componentID');
		}
	}	
	
}
insertExtraComponentTypes();

function insertExtraContentTypes(){
	global $_counterTypes;
	$contentTypes = DB::get(Query::Select('contenttypes')->fields('typeID'));
	if ($contentTypes){
		foreach ($contentTypes as $contentType){
			$_counterTypes['contents_'.$contentType->typeID]=array('table'=>'contents_'.$contentType->typeID, 'field'=>'ID');
		}
	}

}
insertExtraContentTypes();


 
$_counter = array();

function setCount($type,$identifier,$inc){
	_gc(__FUNCTION__);
	_gc('parameters');_d($type,'$type');_d($identifier,'$identifier');_d($inc,'$inc');_u();
	global $_counterTypes;
	global $_counter;
    $type = strtolower($type);
	if (!array_key_exists($type,$_counter)){
		$_counter[$type]=array();
	}
	if (!array_key_exists($identifier,$_counter[$type])){
		$_counter[$type][$identifier]=$inc;
	} else {
		$_counter[$type][$identifier]+=$inc;
	}
	_u();
}

function commitCounts(){
    _gc(__FUNCTION__);
    global $_counterTypes;
    global $_counter;
    _d($_counter);

    $affected_tables = array();
    foreach ($_counterTypes as $type=>$dbData){
        if (array_key_exists($type,$_counter)) {
            foreach ($_counter[$type] as $where=>$useCount){
                if ($useCount != 0){
                    $query = 'UPDATE '.$dbData['table'].' SET useCount = useCount + '.$useCount.' WHERE '.$dbData['field'].'="'.$where.'"';
                    _d($query);
                    DB::query($query);
                    if (!in_array($dbData['table'],$affected_tables)) {
                        $affected_tables[] = $dbData['table'];
                    }

                }
            }
        }
    }

    if (count($affected_tables)) {
        _d($affected_tables, 'affected tables');
        foreach ($affected_tables as $affected_table) {
            $query = Query::Update($affected_table)->pairs(['useCount'=>0])->lt('useCount',0);
            DB::query($query);
        }
    }

    _u();
}
