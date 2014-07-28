<?php
//DB Connection
require_once "Lib/DB/DB.php";
require_once "Lib/DB/Query.php";

$databaseHostName = conf('DB_HOST_NAME');
$databaseName = conf('DB_NAME');
$databaseUser = conf('DB_NAME_ADMIN');
$databasePassword = conf('DB_PASS_ADMIN');

DB::__Init($databaseHostName, $databaseUser, $databasePassword, $databaseName);