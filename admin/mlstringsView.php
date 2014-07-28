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

require_table('mlstrings');

$languageValues = DB::col(Query::Select('languages')->fields('langID')->desc('main')->asc('rank'));

$tableName = 'mlstrings';
$fields = 'ID,strID,index,usedTable,usedID,'.implode(',',$languageValues);
$fieldLabels = 'ID,strID,index,in Table,with ID,'.implode(',',$languageValues);
$pageSize = 50;
$pageNum = 1;
$sortColumn = 'strID' ;
$sortDir = 'ASC';
$searchString = '';
$searchField = DB::val(Query::Select('languages')->fields('langID')->limit(1)->desc('main')->asc('rank'));
$specialFields = 'index,'.implode(',',$languageValues);
$specialData = 'BO';

foreach ($languageValues as $langvalue) {
	if ($specialData != ''){
		$specialData.=',';
	}
	$specialData.='TRIM50';
}

$actions = array(
	array('link'=>'mlstringsEdit.php', 'label'=>'Edit Multilingual String'),
	array('link'=>'mlstringsDelete.php', 'label'=>'Delete', 'rel'=>'This cannot be undone! This may cause text on the site to disappear! Delete this multilingual String?'),
); 


$webPage = webPageConstruct('View Multilingual Strings');
$webPage->find('h1')->before(constructMenu('mlstringsView.php'));
$webPage->find('h1')->after(constructActions($actions));
$webPage->find('h1')->after(constructTable($tableName, $fields, $fieldLabels, $pageSize, $pageNum, $sortColumn, $sortDir, $searchString, $searchField, $specialFields, $specialData));
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);	