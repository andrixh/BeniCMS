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


require_table('interface');


$tableName = 'interface';
$fields = 'ID,strID,value';
$fieldLabels = 'ID,strID,Value';
$pageSize = 50;
$pageNum = 1;
$sortColumn = 'strID' ;
$sortDir = 'ASC';
$searchString = '';
$searchField = 'strID';
$specialFields = 'value';
$specialData = 'DS';




$actions = array(
	array('link'=>'interfaceAdd.php', 'label'=>'Add Interface Text', 'target'=>'noSelect'),
	array('link'=>'interfaceEdit.php', 'label'=>'Edit Interface Text'),
	array('link'=>'interfaceDelete.php', 'label'=>'Delete', 'rel'=>'This cannot be undone! This may cause text on the site to disappear! Delete this interface String?')
); 


$webPage = webPageConstruct('View Interface Texts');
$webPage->find('h1')->before(constructMenu('interfaceView.php'));
$webPage->find('h1')->after(constructActions($actions));
$webPage->find('h1')->after(constructTable($tableName, $fields, $fieldLabels, $pageSize, $pageNum, $sortColumn, $sortDir, $searchString, $searchField, $specialFields, $specialData));
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);	
