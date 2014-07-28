<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');
require_once('Includes/webpage.inc.php');
require_once('Routines/validators.php');

// Init default form variables.
$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];

setError('Unknown Exception - No action performed.',2);

if (($_GET['id'])){
	$id = intval($_GET['id']);
	$result = DB::row(Query::Select('languages')->fields('langID')->eq('ID', $id));
	$langID= $result->langID;
	$name = $result->name;
	if (!$result) { 
		setError('Cannot find language with ID='.$id.' or Database Error.',2);
	}else{
		$result = DB::query(Query::Delete('languages')->eq('id', $id));
		if (!$result) {
			setError('Cannot find language with ID='.$id.' or Database Error.',2);
		}else{
			DB::query('ALTER TABLE mlstrings DROP COLUMN '.$langID);
			setError('Language "'.$langID.'" deleted.',0);
		}
	}	
}

redirect($returnURL);
