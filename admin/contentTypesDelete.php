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
require_once('Routines/selectProviders.php');


require_table('contenttypes');
// Init default form variables.
$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];

setError('Unknown Exception - No action performed.',2);

if (($_GET['id'])){
	$id = intval($_GET['id']);
	$result = DB::row(Query::Select('contenttypes')->fields('typeID','useCount','label','scheme')->eq('ID', $id));
	$useCount = $result->useCount;
	$typeID = $result->typeID;
	$label=$result->label;
	$scheme=$result->scheme;
	countProvidersInPageScheme($scheme,-1);
	if (!$result) { 
		setError('Cannot delete Content Type with ID='.$id.' or Database Error.',2);
	}else{
		if ($useCount == 0){
			$result = DB::query(Query::Delete('contenttypes')->eq('id', $id));
			if (!$result) {
				setError('Cannot find Page Type with ID='.$id.' or Database Error.',2);
			}else{
				DB::query('DROP TABLE contents_'.$typeID);
				commitCounts();
				setError('Content Type "'.$label.'" deleted.',0);

                $templateHtmlSrc = $_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Content/';
                $tplfiles = glob($templateHtmlSrc.ucfirst($typeID).'_*.twig');
                $tplfiles[] = $templateHtmlSrc.ucfirst($typeID).'.twig';
                foreach ($tplfiles as $tplfile) {
                    unlink ($tplfile);
                }
			}
		} else {
			setError('Content Type is in use and cannot be deleted.',2);
		}
	}	
}

redirect($returnURL);