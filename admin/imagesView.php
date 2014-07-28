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



require_table('images');

require_script('Scripts/imageView.js');
$webPage = webPageConstruct('View Images');
$webPage->find('h1')->before(constructMenu('imagesView.php'));

$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);