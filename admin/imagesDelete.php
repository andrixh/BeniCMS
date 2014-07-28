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
require_once('Routines/filesystem.php');
require_once('Routines/images.php');

// Init default form variables.
$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];
$uploadDirectory=$_SERVER['DOCUMENT_ROOT'].conf('IMAGE_CONFORMED_DIRECTORY'); 
$resizedDirectory=$_SERVER['DOCUMENT_ROOT'].conf('IMAGE_RESIZED_DIRECTORY');
setError('Unknown Exception - No action performed.',2);

if (($_GET['id'])){
	$id = intval($_GET['id']);
	$result = DB::row(Query::Select('images')->fields('physicalName','type','useCount','description')->id( $id)->limit(1));
	_d($result);
	$physicalName = $result->physicalName;
	$type = $result->type;
	$useCount = $result->useCount;
	$description = mlString::Create($result->description);
	
	if (!$result) { 
		setError('Cannot find image with ID='.$id.' or Database Error.',2);
	}else{
		if ($useCount == 0){
			$result = DB::query(Query::Delete('images')->eq('id', $id));
			if (!$result) {
				setError('Cannot find image with ID='.$id.' or Database Error.',2);
			}else{
				unlink($uploadDirectory.$physicalName.'.'.$type);
				$description->delete();
				setError('Image deleted.',0);
				deleteResizedImagesCache($physicalName.'.'.$type);
			}
		} else {
			setError('Image is in use and cannot be deleted.',2);
		}
	}	
}

redirect($returnURL);