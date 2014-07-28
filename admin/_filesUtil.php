<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/filesystem.php');
require_once('Routines/images.php');
require_once('Includes/form.inc.php');
require_once('Includes/webpage.inc.php');
//require_once('Includes/errors.inc.php');


if (isset($_GET['action']) && $_GET['action'] == 'list'){
    $files = DB::get(Query::Select('files')->fields('ID','physicalName','fileName','extension','size','useCount'));
	echo json_encode($files);
} else if (isset($_GET['action']) && $_GET['action'] == 'label'){
	if (isset($_GET['id']) && isset($_GET['label'])){
        DB::query(Query::Update('files')->pairs('fileName',$_GET['label'])->eq('ID',$_GET['id']));
	}
} else if (isset($_GET['action']) && $_GET['action'] == 'delete') {
	if (isset($_GET['ids']) && $_GET['ids']!=''){
		$ids = explode(',',$_GET['ids']);
		for ($i = 0; $i<count($ids); $i++){
			$ids[$i] = intval($ids[$i]);
		}
		//$query = 'SELECT ID,physicalName FROM files WHERE useCount < 1 AND ID IN ('.implode(',',$ids).')';
        $query= Query::Select('files')->fields('ID','physicalName')->lt('useCount',1)->w_and()->in('ID',$ids);
        _d($query);
		$deleteables = DB::get($query);
		_d($deleteables);
		$deleteResult = [];
		$deleteFileList = [];
		$filesDirectory = $_SERVER['DOCUMENT_ROOT'].conf('FILE_UPLOAD_DIRECTORY');
		$deletableIDs = [];
		$deletableDescriptions = [];
		foreach ($deleteables as $deleteable){
			$deletableIDs[] = $deleteable->ID;
			$deleteFileList[] = $_SERVER['DOCUMENT_ROOT'].conf('FILE_UPLOAD_DIRECTORY').$deleteable->physicalName;
			$deleteResult[] = $deleteable->ID;
		}
		_d($deleteFileList);
			
		//$deleteQuery = 'DELETE FROM files WHERE ID in ('.implode(',',$deletableIDs).')';
        $deleteQuery = Query::Delete('files')->in('ID',$deletableIDs);
		DB::query($deleteQuery);

		array_map( "unlink", $deleteFileList );
				
		echo json_encode($deleteResult);
	}
} else {//defaults to uploading;
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	// Settings
	$targetDir = $_SERVER['DOCUMENT_ROOT'].conf('FILE_UPLOAD_DIRECTORY');
	//$targetDir = 'uploads';
	
	$cleanupTargetDir = true; // Remove old files
	$maxFileAge = 5 * 3600; // Temp file age in seconds
	
	// 5 minutes execution time
	@set_time_limit(5 * 60);
	
	// Uncomment this one to fake upload time
	// usleep(5000);
	
	// Get parameters
	$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
	$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
	$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
	
	// Clean the fileName for security reasons
	$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);
	
	// Make sure the fileName is unique but only if chunking is disabled
	if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
		$ext = strrpos($fileName, '.');
		$fileName_a = substr($fileName, 0, $ext);
		$fileName_b = substr($fileName, $ext);
	
		$count = 1;
		while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
			$count++;
	
		$fileName = $fileName_a . '_' . $count . $fileName_b;
	}
	
	$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
	
	// Create target dir
	if (!file_exists($targetDir))
		@mkdir($targetDir);
	
	// Remove old temp files	
	if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
		while (($file = readdir($dir)) !== false) {
			$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
	
			// Remove temp file if it is older than the max age and is not the current file
			if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
				@unlink($tmpfilePath);
			}
		}
	
		closedir($dir);
	} else
		die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
		
	
	// Look for the content type header
	if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
		$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
	
	if (isset($_SERVER["CONTENT_TYPE"]))
		$contentType = $_SERVER["CONTENT_TYPE"];
	
	// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
	if (strpos($contentType, "multipart") !== false) {
		if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen($_FILES['file']['tmp_name'], "rb");
	
				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
				fclose($in);
				fclose($out);
				@unlink($_FILES['file']['tmp_name']);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
	} else {
		// Open temp file
		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
		if ($out) {
			// Read binary input stream and append it to temp file
			$in = fopen("php://input", "rb");
	
			if ($in) {
				while ($buff = fread($in, 4096))
					fwrite($out, $buff);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
	
			fclose($in);
			fclose($out);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
	}
	
	// Check if file has been uploaded
	if (!$chunks || $chunk == $chunks - 1) {
		// Strip the temp .part suffix off 
		rename("{$filePath}.part", $filePath);
	}
	
	
	
	
	//conform image and save to db;
	_d($filePath);
	$tmpName = $filePath;
	$size = filesize($tmpName);
	$nameParts = pathinfo($tmpName);
	$savedFileName=strtolower($nameParts['filename']);
	$description = '';
		
	$uploadDirectory=$_SERVER['DOCUMENT_ROOT'].conf('FILE_UPLOAD_DIRECTORY'); 

	$valid=true; // no detected errors as a start - success assumed
	
	//if ((!isset($_FILES['file'])) || ($_FILES['file']['error']!='0')) {
		//$valid = false;
		//addFormError(1,'Unknown error. Upload not completed');
	//} else {
		$tmpName = $filePath;
		$fileParts = explode('.',$filePath);
		//$size = $_FILES['file']['size'];
		$extension = $fileParts[count($fileParts)-1];
		//$maxSize = conf('IMAGE_MAX_UPLOAD_SIZE')*1024*1024;
		//if ($size > $maxSize){
		//	$valid = false;
		//	addFormError(1,'File size too large. Maximum allowed size is '.conf('IMAGE_MAX_UPLOAD_SIZE').' MB');	
		//} else if (count($fileParts)<2){ //---is there an extension
		//	$valid = false;
		//	addFormError(1,'Unrecognized file type. File Upload Rejected');	
		//} else if (!in_array(mb_strtolower($extension),explode(',',conf('IMAGE_ALLOWED_EXTENSIONS')))){
		//	$valid = false;
		//	addFormError(1,'Image type not allowed. File Upload Rejected');	
		//}
	//}

	//if (strlen($label) < 1 || strlen($label)>255 || !allowedChars($label,conf('ALPHANUM').'_-., ')) {
	//	$valid = false;
	//	addFormError(2,'Must be 1-255 charachters long, and contain only common charachters.');
	//}	
	
	if ($valid){ //if no errors, insert into database
		$physicalName = generateUniqueFileName($uploadDirectory);

		$queryFields = [
			'fileName'=>$savedFileName,
			'extension'=>$extension,
			'physicalName'=>$physicalName,
			'size'=>$size,
			'useCount'=>0
		];

        $query = Query::Insert('files')->pairs($queryFields);
		$result = DB::query($query);
		$queryFields['ID']=DB::insert_id();
		
		_g('moving uploaded file: '.$tmpName);
		_d(is_uploaded_file($tmpName),'is uploaded');
		_d($uploadDirectory.$physicalName);
		$test = rename($tmpName,$uploadDirectory.$physicalName);
		_d($test);
		_u();		
	}

	// Return JSON-RPC response
	die('{"jsonrpc" : "2.0", "result" : null, "id" : "id", "newFile":'.json_encode($queryFields).'}');
	
}


