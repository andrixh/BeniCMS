<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/form.inc.php');
require_once('Routines/validators.php');
require_once('Routines/filesystem.php');
require_once('Routines/mlstrings.php');

$mimeTypes = array(
	'txt'=>'application/plain',
	'rtf'=>'application/rtf',
	'pdf'=>'application/pdf',
	'doc'=>'application/msword',
	'docx'=>'application/msword',
	'xls'=>'application/excel',
	'xlsx'=>'application/excel',
	'ppt'=>'application/powerpoint',
	'pptx'=>'application/powerpoint',
	'pps'=>'application/powerpoint',
	'ppsx'=>'application/powerpoint',
	'rar'=>'application/x-rar-compressed',
	'zip'=>'application/zip'
);



if (isset($_GET['id'])){
	$id = $_GET['id'];
	$currentRecord =BD::row(Query::Select('files')->eq('id', $id)->limit(1));
	$physicalName = $currentRecord->physicalName;
	$fileName = $currentRecord->fileName;
	$extension = $currentRecord->extension;
	$size = $currentRecord->size;
	$description = mlstring($currentRecord->description);
	$useCount = $currentRecord->useCount;
	$protect = $currentRecord->protect;
} 




$outputFileName = $fileName.'.'.$extension;

$file = $_SERVER['DOCUMENT_ROOT'].conf('FILE_UPLOAD_DIRECTORY').$physicalName;


if(file_exists($file)){
    // Set headers
    header("Cache-Control: public");
   header("Content-Description: File Transfer");
   header('Content-Disposition: attachment; filename='.$outputFileName);
  header("Content-Type: ".$mimeTypes[$extension]);
		header("Content-Transfer-Encoding: binary");
		// Read the file from disk
    readfile($file);
}