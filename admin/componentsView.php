<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/db.tables.inc.php');
require_once('Includes/errors.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/table.inc.php');
require_once('Routines/validators.php');

$type=$_GET['type'];
$tableName = 'components_'.$type;

$scheme = json_decode(DB::val(Query::Select('componenttypes')->fields('scheme')->eq('typeID', $type)->limit(1)));
_d($scheme,'SCHEME!!!');
$_fields = array('ID','componentID','useCount');
$_fieldLabels = array('ID','Component ID','Use');

$_specialFields = array();
$_specialData = array();

foreach ($scheme as $schemeField){
	if ($schemeField->visible){
		$_fields[] = $schemeField->name;
		$_fieldLabels[] = $schemeField->label;
		if (in_array($schemeField->type, array('mlstring','mlhtml','mlfiles'))){
			$_specialFields[]=$schemeField->name;
			$_specialData[]='DS';
		} else if (in_array($schemeField->type, array('mlgallery','gallery'))){
			$_specialFields[]=$schemeField->name;
			$_specialData[]='IMG';
		} else if ($schemeField->type == 'boolean'){
			$_specialFields[]=$schemeField->name;
			$_specialData[]='BO_T';
        } else if ($schemeField->type == 'number' && strtolower($schemeField->name) == 'rank'){
            $_specialFields[]=$schemeField->name;
            $_specialData[]='RK';
        }
	}
}

require_table($tableName);

$typeLabel = ucfirst(DB::val(Query::Select('componenttypes')->fields('label')->eq('typeID', $type)->limit(1)));

//require_table($tableName,'_component');

$label = ucfirst(DB::val(Query::Select('componenttypes')->fields('label')->eq('typeID', $type)->limit(1)));


$tableName = 'components_'.$type;
$fields = implode(',',$_fields);
$fieldLabels = implode(',',$_fieldLabels);
$pageSize = 200;
$pageNum = 1;
$sortColumn = 'ID' ;
$sortDir = 'ASC';
$searchString = '';
$searchField = 'ID';
$specialFields = implode(',',$_specialFields);
$specialData = implode(',',$_specialData);


$actions = array(
	array('link'=>'componentsAdd.php?type='.$type, 'label'=>'Add '.$label.' Component', 'target'=>'noSelect'),
	array('link'=>'componentsEdit.php?type='.$type, 'label'=>'Edit '.$label.' Component'),
	array('link'=>'componentsDelete.php?type='.$type, 'label'=>'Delete', 'rel'=>'This cannot be undone! Are you sure you want to delete this '.$label.' Component?')
); 


$webPage = webPageConstruct('View '.$label.' Components');
$webPage->find('h1')->before(constructMenu('componentsView.php?type='.$type));
$webPage->find('h1')->after(constructActions($actions));
$webPage->find('h1')->after(constructTable($tableName, $fields, $fieldLabels, $pageSize, $pageNum, $sortColumn, $sortDir, $searchString, $searchField, $specialFields, $specialData));
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);	

