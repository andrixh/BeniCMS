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
$tableName = 'contents_'.$type;

$scheme = json_decode(DB::val(Query::Select('contenttypes')->fields('scheme')->eq('typeID', $type)->limit(1)));
_d($scheme,'Loaded Scheme');
$_fields = array('ID','contentID','useCount');
$_fieldLabels = array('ID','contentID','Use');

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

$typeLabel = ucfirst(DB::val(Query::Select('contenttypes')->fields('label')->eq('typeID', $type)->limit(1)));

$tableName = 'contents_'.$type;
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
	array('link'=>'contentsAdd.php?type='.$type, 'label'=>'Add '.$typeLabel.' Content', 'target'=>'noSelect'),
	array('link'=>'contentsEdit.php?type='.$type, 'label'=>'Edit '.$typeLabel.' Content'),
	array('link'=>'contentsDelete.php?type='.$type, 'label'=>'Delete', 'rel'=>'This cannot be undone! Are you sure you want to delete this '.$typeLabel.' Content?')
); 


$webPage = webPageConstruct('View '.$typeLabel.' Contents');
$webPage->find('h1')->before(constructMenu('contentsView.php?type='.$type));
$webPage->find('h1')->after(constructActions($actions));
$webPage->find('h1')->after(constructTable($tableName, $fields, $fieldLabels, $pageSize, $pageNum, $sortColumn, $sortDir, $searchString, $searchField, $specialFields, $specialData));
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);	

