<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');


$images = DB::get(Query::Select('images')->fields('ID','physicalName','type','label','width','height','useCount','description'));

	$imagesPath = conf('IMAGE_RESIZED_DIRECTORY');
	
	$metadata = ['imagePath'=>$imagesPath];
	
	



echo json_encode(['metadata'=>$metadata,'contents'=>$images]);