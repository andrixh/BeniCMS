<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/filesystem.php');
require_once('Routines/images.php');
require_once('Includes/form.inc.php');
require_once('Includes/webpage.inc.php');
//require_once('Includes/errors.inc.php');



$componentTypes = DB::get(Query::Select('componenttypes')->fields('typeID','label'));
$contentTypes = DB::get(Query::Select('contenttypes')->fields('typeID','label'));

echo json_encode(['components'=>$componentTypes,'contents'=>$contentTypes]);


