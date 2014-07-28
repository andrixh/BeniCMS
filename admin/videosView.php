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


require_table('videos');


$tableName = 'videos';
$fields = 'ID,thumbnail,label,service,videoID,description,useCount,ownThumbnail';
$fieldLabels = 'ID,Thumbnail,Label,Service,Video ID,Description,Use,Own Thumb';
$pageSize = 50;
$pageNum = 1;
$sortColumn = 'label' ;
$sortDir = 'ASC';
$searchString = '';
$searchField = 'label';
$specialFields = 'thumbnail,service,description,ownThumbnail';
$specialData = 'IMG,Gfx/VideoServices,DS,BO';




$actions = array(
	array('link'=>'videosAdd.php', 'label'=>'Add Video', 'target'=>'noSelect'),
	array('link'=>'videosEdit.php', 'label'=>'Edit Video'),
	array('link'=>'videosDelete.php', 'label'=>'Delete', 'rel'=>'This cannot be undone! Delete this Video?')
); 


$webPage = webPageConstruct('View Videos');
$webPage->find('h1')->before(constructMenu('videosView.php'));
$webPage->find('h1')->after(constructActions($actions));
$webPage->find('h1')->after(constructTable($tableName, $fields, $fieldLabels, $pageSize, $pageNum, $sortColumn, $sortDir, $searchString, $searchField, $specialFields, $specialData));
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);	
