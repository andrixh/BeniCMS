<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/templateHelper.class.php');

$result = [];

$pageTypes = DB::get(Query::Select('pagetypes')->fields('typeID','label','icon','comment')->asc('rank'));

echo json_encode($pageTypes);
