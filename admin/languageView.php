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

$tableName = 'languages';
$fields = 'ID,langID,active,main,rank';
$fieldLabels = 'ID,LangID,Active,Main,Rank';
$pageSize = 10;
$pageNum = 1;
$sortColumn = 'Rank' ;
$sortDir = 'ASC';
$searchString = '';
$searchField = 'Name';
$specialFields = 'active,main,rank';
$specialData = 'BO_T,BO_X,RK';

require_table('languages');

$actions = array(
	array('link'=>'languageAdd.php', 'label'=>'Add Language', 'target'=>'noSelect'),
	array('link'=>'languageDelete.php', 'label'=>'Delete Language', 'rel'=>'This cannot be undone! You will lose this language entry as well as all StringTable text associated with it! Are you sure you want to delete this language?', 'rev'=>'languageView.php')
); 

$webPage = webPageConstruct('View Languages');
$webPage->find('h1')->before(constructMenu('languageView.php'));
$webPage->find('h1')->after(constructActions($actions));
$webPage->find('h1')->after(constructTable($tableName, $fields, $fieldLabels, $pageSize, $pageNum, $sortColumn, $sortDir, $searchString, $searchField, $specialFields, $specialData));
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);	

