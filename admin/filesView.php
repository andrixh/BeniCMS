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


require_table('files');



require_script('Scripts/filesView.js');
$webPage = webPageConstruct('View Files');
$webPage->find('h1')->before(constructMenu('filesView.php'));

$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);