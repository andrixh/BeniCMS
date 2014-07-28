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

// Init default form variables.
$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];
$uploadDirectory=$_SERVER['DOCUMENT_ROOT'].conf('FILE_UPLOAD_DIRECTORY');

setError('Unknown Exception - No action performed.',2);

if (($_GET['id'])){
	$id = intval($_GET['id']);
	$result = DB::row(Query::Select('files')->fields('fileName','extension','physicalName','useCount','protect','description')->eq('ID', $id));
	$useCount = $result->useCount;
	$physicalName = $result->physicalName;
	$filename=$result->fileName;
	$extension=$result->extension;
	$description =$result->description;
	$protect = $result->protect;
	
	if (!$result) { 
		setError('Cannot find file with ID='.$id.' or Database Error.',2);
	}else{
		if ($useCount == 0){
			if ($protect==false){
				$result = DB::query(Query::Delete('files')->eq('id', $id));
				if (!$result) {
					setError('Cannot find file with ID='.$id.' or Database Error.',2);
				}else{
					unlink($uploadDirectory.$physicalName);
					deleteDS($description);
					setError('File "'.$filename.'.'.$extension.'" deleted.',0);
				}
			} else {
				setError('File is protected and cannot be deleted.',2);
			}
		} else {
			setError('File is in use and cannot be deleted.',2);
		}
	}	
}

redirect($returnURL);