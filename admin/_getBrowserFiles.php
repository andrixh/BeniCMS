<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');

$files = DB::get(Query::Select('files')->fields('physicalName','extension','fileName'));
$metadata = [];

echo json_encode(['metadata'=>$metadata,'contents'=>$files]);