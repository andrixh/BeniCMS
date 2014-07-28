<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/table.inc.php');
require_once('Routines/validators.php');

$tableName = 'selectproviders';
$fields = 'ID,providerID,label,useCount,editorUrl';
$fieldLabels = 'ID,Provider ID,Label,Use,External';
$pageSize = 50;
$pageNum = 1;
$sortColumn = 'Label' ;
$sortDir = 'ASC';
$searchString = '';
$searchField = 'Label';
$specialFields = 'editorUrl';
$specialData = 'BO';

$actions = array(
	array('link'=>'selectProvidersAdd.php', 'label'=>'Add Provider', 'target'=>'noSelect'),
	array('link'=>'selectProvidersEdit.php', 'label'=>'Edit Provider'),
	array('link'=>'selectProvidersDelete.php', 'label'=>'Delete'),
); 

setError('Warning! Modifying contents of this section may severely compromise site integrity',1,false);
 

$webPage = webPageConstruct('View Select Control Providers');
$webPage->find('h1')->before(constructMenu('selectProvidersView.php'));
$webPage->find('h1')->after(constructActions($actions));
$webPage->find('h1')->after(constructTable($tableName, $fields, $fieldLabels, $pageSize, $pageNum, $sortColumn, $sortDir, $searchString, $searchField, $specialFields, $specialData));
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);