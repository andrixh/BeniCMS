<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/images.php');

//-----populate variables and construct query------
$tableName = $_GET['t']; // basic input
$field = $_GET['f'];
$id = $_GET['id'];
$exclusive = $_GET['x'];


$currVal = DB::val(Query::Select($tableName)->id($id)->fields($field)->id($id));

if ($exclusive) {
    if (!$currVal) {
        DB::query(Query::Update($tableName)->pairs($field,0));
        DB::query(Query::Update($tableName)->pairs($field,1)->id($id));
    }
} else {
    DB::query(Query::Update($tableName)->pairs($field, ($currVal?0:1))->id($id));
}









