<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/db.tables.inc.php');
require_once('Includes/errors.inc.php');
require_once('Includes/counter.inc.php');
require_once('Includes/webpage.inc.php');
require_once('Routines/validators.php');
require_once('Routines/mlstrings.php');
require_once('Routines/selectProviders.php');


require_table('pagetypes');
// Init default form variables.
$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];

setError('Unknown Exception - No action performed.',2);

if (($_GET['id'])){
	$id = intval($_GET['id']);
	$result = DB::row(Query::Select('pagetypes')->fields('typeID','useCount','label','scheme')->eq('ID', $id));
	$useCount = $result->useCount;
	$typeID = $result->typeID;
	$label=$result->label;
	$scheme=$result->scheme;
	countProvidersInPageScheme($scheme,-1);
	if (!$result) { 
		setError('Cannot delete Page Type with ID='.$id.' or Database Error.',2);
	}else{
		if ($useCount == 0){
			$result = DB::query(Query::Delete('pagetypes')->eq('id', $id));
			if (!$result) {
				setError('Cannot find Page Type with ID='.$id.' or Database Error.',2);
			}else{

				if (file_exists($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Page/'.ucfirst($typeID).'.twig')){
					unlink($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Page/'.ucfirst($typeID).'.twig');
				}
				if (file_exists($_SERVER['DOCUMENT_ROOT'].'/ContentClasses/Page/'.ucFirst($typeID).'.php')){
					unlink($_SERVER['DOCUMENT_ROOT'].'/ContentClasses/Page/'.ucFirst($typeID).'.php');
				}		
				commitCounts();
                DB::query('DROP TABLE `pages_'.$typeID.'`');
				setError('Page Type "'.$label.'" deleted.',0);
			}
		} else {
			setError('Page Type is in use and cannot be deleted.',2);
		}
	}	
}

redirect($returnURL);