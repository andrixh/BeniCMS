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
	$result = DB::row(Query::Select('mlstrings')->fields('strID','used')->eq('ID', $id));
	$strID= $result->strID;

	if (!$result) { 
		setError('Cannot find Dynamic String with ID='.$id.' or Database Error.',2);
	}else{
		$used = $result->used;
		if ($used == 0){
			$result = DB::query(Query::Delete('mlstrings')->eq('id', $id));
			if (!$result) {
				setError('Cannot find Dynamic String with ID='.$id.' or Database Error.',2);
			}else{
				setError('Dynamic String "'.$strID.'" deleted.',0);
			}
		} else {
			setError('Dynamic string is in use and cannot be deleted.',2);
		}
	}	
}

redirect($returnURL);