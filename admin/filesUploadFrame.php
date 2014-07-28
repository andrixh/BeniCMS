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

$perform=false;
// Init default form variables.
$file='';
$fileName='';
$description=mlstring_new();
$protect = false;

$id = -1;

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$fileName = $_POST['fileName'];
	$description = mlstring_fromPost('description');
	$protect=isset($_POST['protect'])?true:false;
	
	$uploadDirectory=$_SERVER['DOCUMENT_ROOT'].conf('FILE_UPLOAD_DIRECTORY');

	$valid=true; // no detected errors as a start - success assumed
	
	if ((!isset($_FILES['file'])) || ($_FILES['file']['error']!='0')) {
		$valid = false;
		addFormError(1,'Unknown error. Upload not completed');
	} else {
		$tmpName = $_FILES['file']['tmp_name'];
		$fileParts = explode('.',$_FILES['file']['name']);
		$size = $_FILES['file']['size'];
		$extension = $fileParts[count($fileParts)-1];
		$maxSize = conf('FILE_MAX_UPLOAD_SIZE')*1024*1024;
		if ($size > $maxSize){
			$valid = false;
			addFormError(1,'File too large. Maximum allowed size is '.conf('FILE_MAX_UPLOAD_SIZE').' MB');	
		} else if (count($fileParts)<2){ //---is there an extension
			$valid = false;
			addFormError(1,'Unrecognized file type. File Upload Rejected');	
		} else if (!in_array(mb_strtolower($extension),explode(',',conf('FILE_ALLOWED_EXTENSIONS')))){
			$valid = false;
			addFormError(1,'File Type not allowed. File Upload Rejected');	
		}
	}
	

	if (strlen($fileName) < 1 || strlen($fileName)>50 || !allowedChars($fileName,conf('ALPHANUM').'_-. ')) {
		$valid = false;
		addFormError(2,'Must be 1-50 charachters long, and contain only common charachters.');
	}	
	
	if ($valid){ //if no errors, insert into database
		$physicalName = generateUniqueFileName($uploadDirectory);
		addDS($description);
		

		$queryFields = array(
			'fileName'=>$fileName,
			'extension'=>$extension,
			'physicalName'=>$physicalName,
			'size'=>$size,
			'useCount'=>0,
			'protect'=>$protect,
			'description'=>$description['strID'],
		);
		$result = DB::query(Query::Insert('files')->pairs($queryFields));
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			move_uploaded_file($tmpName,$uploadDirectory.$physicalName);
			setError('File "'.$fileName.'.'.$extension.'" uploaded sucessfully',0);
			//prepare minimal webPage for notification
			require_script('Scripts/iframe.js');
			$form = formConstruct('Upload');
			$webPage = webPageConstruct('Upload File');
			$webPage->find('h1')->after('<a class="iframeClose" href="#">x</a>');
			$webPage->find('h1')->after(generateMessageBar());
			$webPage->find('h1')->remove();
			echo outputWebPage($webPage);	
			die();
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}



$form = formConstruct('Upload');
$fieldset1='<fieldset class="col1 first">';
$fieldset1.=field(label('Select File','Only '.conf('FILE_ALLOWED_EXTENSIONS')),control_file('file'),1);
$fieldset1.=field(label('File Name','no extension'),control_textInput('fileName', $fileName),2);
$fieldset1.='</fieldset><fieldset class="col1">';
$fieldset1.=field(label('Description'),control_mlTextInput('description', $description));
$fieldset1.=field(label_checkbox('Protect'),control_checkbox('protect', $protect));
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);
$form->find('fieldset.submit')->addClass('hidden');

$form->find('#file')->addClass('checkExtension')->attr('data-target','#fileName')->attr('data-extensions',conf('FILE_ALLOWED_EXTENSIONS'));
require_script('Scripts/uploadUtils.js');


require_script('Scripts/iframe.js');
$webPage = webPageConstruct('Upload File');
$webPage->find('h1')->after('<a class="iframeClose" href="#">x</a>');
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
$webPage->find('h1')->remove();
echo  outputWebPage($webPage);	
