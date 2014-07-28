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

require_table('componenttypes');

$tableName = 'componenttypes';
$fields = 'ID,icon,typeID,label,useCount,comment,rank,hidden';
$fieldLabels = 'ID,Icon,Type ID,Label,Use,Comment,Rank,Hidden';
$pageSize = 50;
$pageNum = 1;
$sortColumn = 'Label' ;
$sortDir = 'ASC';
$searchString = '';
$searchField = 'Label';
$specialFields = 'icon,hidden,rank';
$specialData = 'Gfx/PageTypes/16,BO_T,RK';

$actions = array(
	array('link'=>'componentTypesAdd.php', 'label'=>'Add Component Type', 'target'=>'noSelect'),
	array('link'=>'componentTypesEdit.php', 'label'=>'Edit Component Type'),
	array('link'=>'componentTypesDelete.php', 'label'=>'Delete', 'rel'=>'Delete this component type?'),
); 

setError('Warning! Modifying contents of this section may severely compromise site integrity',1,false);
 
$webPage = webPageConstruct('View Component Types');
$webPage->find('h1')->before(constructMenu('componentTypesView.php'));
$webPage->find('h1')->after(constructActions($actions));
$webPage->find('h1')->after(constructTable($tableName, $fields, $fieldLabels, $pageSize, $pageNum, $sortColumn, $sortDir, $searchString, $searchField, $specialFields, $specialData));
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);