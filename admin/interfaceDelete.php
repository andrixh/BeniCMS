<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');
require_once('Includes/webpage.inc.php');
require_once('Routines/validators.php');
require_once('Routines/mlstrings.class.php');

// Init default form variables.
$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];

setError('Unknown Exception - No action performed.',2);

if (($_GET['id'])){
	$id = intval($_GET['id']);
	$currentRecord = DB::row(Query::Select('interface')->fields('strID','value')->eq('ID', $id)->limit(1));
	$strID = $currentRecord->strID;
	if (!$currentRecord) { 
		setError('Cannot find interface string with ID='.$id.' or Database Error.',2);
	}else{
		$value = mlString::Create($currentRecord->value);
		$result = DB::query(Query::Delete('interface')->eq('id', $id));
		if (!$result) {
			setError('Cannot find Interface string with ID='.$id.' or Database Error.',2);
		}else{
			$value->delete();
			setError('Interface String "'.$strID.'" deleted.',0);
		}
	}	
}

redirect($returnURL);