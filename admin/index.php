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

require_table('admins');
require_table('admins_resetpassword');
require_table('site');
require_table('languages');
require_table('interface');
require_table('mlstrings');
require_table('files');
require_table('images');
require_table('videos');
require_table('selectproviders');
require_table('pages');
require_table('pagetypes');
require_table('contenttypes');
require_table('componenttypes');


require_style('Css/admin.css');
$webPage = webPageConstruct('Welcome, '.$_SESSION['loginFullName']);
$webPage->find('h1')->before(constructMenu());
echo  outputWebPage($webPage);
