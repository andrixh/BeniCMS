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

require_table('admins');


$tableName = 'admins';
$fields = 'ID,UserName,FullName,Email,Active';
$fieldLabels = 'ID,User Name,Full Name,Email,Active';
$pageSize = 10;
$pageNum = 1;
$sortColumn = 'UserName' ;
$sortDir = 'ASC';
$searchString = '';
$searchField = 'UserName';
$specialFields = 'Active';
$specialData = 'BO';

$actions = [
    ['link'=>'adminAdd.php', 'label'=>'Add Administrator', 'target'=>'noSelect'],
	['link'=>'adminEdit.php', 'label'=>'Edit Administrator'],
	['link'=>'adminDelete.php', 'label'=>'Delete Administrator', 'rel'=>'This cannot be undone! Delete this administrator?', 'rev'=>'adminView.php']
];

$webPage = webPageConstruct('Administrators');
$webPage->find('h1')->before(constructMenu('adminView.php'));
$webPage->find('h1')->after(constructActions($actions));
$webPage->find('h1')->after(constructTable($tableName, $fields, $fieldLabels, $pageSize, $pageNum, $sortColumn, $sortDir, $searchString, $searchField, $specialFields, $specialData));
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);

