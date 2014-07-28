<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');
require_once('Includes/webpage.inc.php');
require_once('Routines/validators.php');
require_once('Routines/mlstrings.php');
require_once('Routines/selectProviders.php');

// Init default form variables.
$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];

setError('Unknown Exception - No action performed.',2);

if (($_GET['id'])){
	$id = intval($_GET['id']);
	$result = DB::row(Query::Select('selectproviders')->fields('providerID','useCount','label','editorUrl')->eq('ID', $id));
	$useCount = $result->useCount;
	$providerID = $result->providerID;
	$label=$result->label;
	$editorUrl=$result->editorUrl;
	
	if (!$result) { 
		setError('Cannot delete Select Provider with ID='.$id.' or Database Error.',2);
	}else{
		if ($useCount == 0 && $editorUrl==''){
			$result = DB::query(Query::Delete('selectproviders')->eq('id', $id));
			if (!$result) {
				setError('Cannot find Select Provider with ID='.$id.' or Database Error.',2);
			}else{
				setError('Select Provider "'.$label.'" deleted.',0);
			}
		} else {
			setError('Select Provider is in use or is external and cannot be deleted.',2);
		}
	}	
}

redirect($returnURL);