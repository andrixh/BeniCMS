<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/db.tables.inc.php');
require_once('Includes/errors.inc.php');
require_once('Includes/webpage.inc.php');
require_once('Routines/validators.php');


require_table('admins');

// Init default form variables.
$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];

setError('Unknown Exception - No action performed.',2);



if (($_GET['id'])){
	$id = intval($_GET['id']);
	$result = DB::row(Query::Select('admins')->fields('userName','role')->eq('id', $id));
	if (!$result) {
		setError('Cannot find user with ID='.$id.' or Database Error.',2);
	}else{
		$role = $result->role;
		rolePass($role,'You are not authorized to delete this administrator');
		$userName = $result->userName;
		$result = DB::query(Query::Delete('admins')->eq('id', $id));
		if (!$result) {
			setError('Cannot find administrator with ID='.$id.' or Database Error.',2);
		}else{
			setError('Administrator "'.$userName.'" deleted from database.',0);
		}
	}	
}

redirect($returnURL);
