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
	$images = DB::get(Query::Select('images')->fields('ID','physicalName','type','label','width','height','useCount','description')->asc('label'));
	//$imagesPath = conf('IMAGE_RESIZED_DIRECTORY');
	
	//$metadata = array('imagePath'=>$imagesPath);
	
	echo json_encode($images);
} else if (isset($_GET['action']) && $_GET['action'] == 'label'){
	if (isset($_GET['id']) && isset($_GET['label'])){
        DB::query(Query::Update('images')->pairs('label',$_GET['label'])->eq('ID',$_GET['id']));
	}
} else if (isset($_GET['action']) && $_GET['action'] == 'delete') {
	if (isset($_GET['ids']) && $_GET['ids']!=''){
		$ids = explode(',',$_GET['ids']);
		for ($i = 0; $i<count($ids); $i++){
			$ids[$i] = intval($ids[$i]);
		}
        $deletables = DB::get(Query::Select('images')->fields('ID','physicalName','type','description')->lt('useCount',1)->w_and()->in('ID',$ids));
		_d($deletables);
		$deleteResult = [];
		$deleteFileList = [];
		$resizedDirectory = $_SERVER['DOCUMENT_ROOT'].conf('IMAGE_RESIZED_DIRECTORY');
		$deletableIDs = [];
		$deletableDescriptions = [];
		foreach ($deletables as $deletable){
			$deletableIDs[] = $deletable->ID;
			if ($deletable->description != ''){
				$deletableDescriptions[] = "'".$deletable->description."'";
			}
			$deleteFileList[] = $_SERVER['DOCUMENT_ROOT'].conf('IMAGE_CONFORMED_DIRECTORY').$deletable->physicalName.'.'.$deletable->type;
			$resizedFiles = glob($_SERVER['DOCUMENT_ROOT'].conf('IMAGE_RESIZED_DIRECTORY').$deletable->physicalName.'*.*');
			$deleteFileList = array_merge($deleteFileList, $resizedFiles);
			$deleteResult[] = $deletable->ID;
		}
		_d($deleteFileList);
			
        DB::query(Query::Delete('images')->in('ID',$deletableIDs));
		_d($deletableDescriptions,'$deletableDescriptions');
		_d(count($deletableDescriptions));
		if (count($deletableDescriptions) > 0){
            DB::query(Query::Delete('mlstrings')->in('strID',$deletableDescriptions));
		}
		
		array_map( "unlink", $deleteFileList );
		
		
		echo json_encode($deleteResult);
	}
} else if (isset($_GET['action']) && $_GET['action'] == 'getDescriptionForm') {
	$result = field(label('Description'),control_mlTextArea('description', mlString::Create()->getValues()));	
	//$result = control_mlTextInput('description', mlString::Create()->getValues());
	echo $result;
} else if (isset($_GET['action']) && $_GET['action'] == 'getDescription') {
	if (isset($_GET['id'])){
		$descStrID = DB::val(Query::Select('images')->fields('description')->eq('ID',$_GET['id']));
		$mlstring = mlString::Create($descStrID);
		echo json_encode($mlstring->getValues());
	}
} else if (isset($_GET['action']) && $_GET['action'] == 'setDescription') {
	if (isset($_GET['ids'])){
		_d($_POST,'POST');
		_d($_GET,'GET');
		
		$idList = explode(',', $_GET['ids']);
		_d($idList,'idList');
		$currentDescriptions = DB::get(Query::Select('images')->fields('ID','description')->in('ID',$idList));
		_d($currentDescriptions,'$currentDescriptions');
		$descStrIDs=[];
		$descStrIDs_out=[];
		foreach ($currentDescriptions as $currDesc){
			$descStrIDs[$currDesc->ID]=$currDesc->description;
		}
		_d($descStrIDs);
		foreach ($descStrIDs as $id=>$strID){
			_d('ID '.$id.'  Original_strID'.$strID);
			$desc = mlString::Create($strID)->postName('description')->usedTable('images')->usedID($id)->fromPost();


			_d($desc,'desc mlstring');
            DB::query(Query::Update('images')->pairs(['description'=>$desc->strID])->eq('ID',$id));
			$desc->save();
			$descStrIDs_out[$id]=$desc->strID;
			
		}
		echo (json_encode($descStrIDs_out));
	}
} else {//defaults to uploading;
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	// Settings
	$targetDir = conf('IMAGE_UPLOAD_DIRECTORY');//ini_get("upload_tmp_dir");// . DIRECTORY_SEPARATOR . "plupload";
	_d($targetDir,'$targetDir');
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
	$label='(untitled)';
	$description = '';
		
	$uploadDirectory=$_SERVER['DOCUMENT_ROOT'].conf('IMAGE_CONFORMED_DIRECTORY'); 

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
		//Perform Image Resize and storage
		$maxWidth = conf('IMAGE_CONFORM_WIDTH');
		$maxHeight = conf('IMAGE_CONFORM_HEIGHT');
		list($width,$height)=getimagesize($tmpName);
		
		if ($width > $maxWidth || $height > $maxHeight) {
			$newWidth = $maxWidth;
			$newHeight=($height/$width)*$maxWidth;
			if ($newHeight > $maxHeight){
				$newHeight = $maxHeight;
				$newWidth =($width/$height)*$maxHeight;
			}
		} else {
			$newWidth = $width;
			$newHeight = $height;
		}
		
		if (strtolower($extension) != 'png'){ //file is a JPEG
			$extension = 'jpg';
		} else {
			$extension = 'png';
		}
		_d($tmpName);
		_d($extension);
		$imageOriginal = ($extension=='jpg')?imagecreatefromjpeg($tmpName):imagecreatefrompng($tmpName);
		_d('created Original Image');
		$imageNew=imagecreatetruecolor($newWidth,$newHeight); //create new image canvas
		_d('created New Image Canvas');
		imagealphablending($imageNew,false);
		imagecopyresampled($imageNew,$imageOriginal,0,0,0,0,$newWidth,$newHeight,$width,$height);
		$physicalName = generateUniqueFileName($uploadDirectory,$extension);
		_d($physicalName);
		imagesavealpha( $imageNew, true);
		if ($extension == 'png'){
			_d('saving png: '.$uploadDirectory.$physicalName.'.png');
			imagepng($imageNew,$uploadDirectory.$physicalName.'.png', 9); // save as png with maximum compression;
		} else {
			_d('saving jpg: '.$uploadDirectory.$physicalName.'.jpg');
			imagejpeg($imageNew,$uploadDirectory.$physicalName.'.jpg',100); //save as jpeg with quality of 100	
		}	
	
		// Perform database operations after image storage
		$nameParts = pathinfo($tmpName);
		_d($nameParts);
		$label = strtolower($nameParts['filename']);
		$queryFields = [
			'label'=>$label,
			'type'=>$extension,
			'physicalName'=>$physicalName,
			'width'=>$newWidth,
			'height'=>$newHeight,
			'useCount'=>0,
			'description'=>'',
		];
		
        $result = DB::query(Query::Insert('images')->pairs($queryFields));
		$queryFields['ID']=DB::insert_id();
	}
	
	unlink($tmpName);
	
	// Return JSON-RPC response
	die('{"jsonrpc" : "2.0", "result" : null, "id" : "id", "newFile":'.json_encode($queryFields).'}');
	
}
//echo json_encode(array('metadata'=>$metadata,'contents'=>$images));


