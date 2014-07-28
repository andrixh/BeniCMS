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
require_once('Routines/mlstrings.class.php');
require_once('Routines/images.php');

$perform=false;

if (isset($_GET['id'])){
	$id = $_GET['id'];
	$currentRecord =DB::row(Query::Select('images')->fields('physicalName','type')->eq('id', $id)->limit(1));
	$physicalName = $currentRecord->physicalName;
	$type = $currentRecord->type;
	$duplicate = true;
	$angle=0;
	$cropX=0;
	$cropY=0;
	$cropW=0;
	$cropH=0;
	$flipH='false';
	$flipV='false';
} 

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$id = $_POST['id'];
	$currentRecord =DB::row(Query::Select('images')->fields('physicalName','type','description','label')->eq('id', $id)->limit(1));
	$physicalName = $currentRecord->physicalName;
	$type = $currentRecord->type;
	$duplicate=isset($_POST['duplicate'])?true:false;
	$angle=$_POST['angle'];
	$cropX=$_POST['cropX'];
	$cropY=$_POST['cropY'];
	$cropW=$_POST['cropW'];
	$cropH=$_POST['cropH'];
	$flipH=($_POST['flipH']=='true')?true:false;
	$flipV=($_POST['flipV']=='true')?true:false;

	global $db;
	
	$uploadDirectory=$_SERVER['DOCUMENT_ROOT'].conf('IMAGE_CONFORMED_DIRECTORY'); 
	$resizedDirectory=$_SERVER['DOCUMENT_ROOT'].conf('IMAGE_RESIZED_DIRECTORY');
	$fileName = $uploadDirectory.$physicalName.'.'.$type;
	$imageOriginal = ($type=='jpg')?imagecreatefromjpeg($fileName):imagecreatefrompng($fileName);
	list($width,$height)=getimagesize($fileName);
	//flip
	$imageFlip=imagecreatetruecolor($width,$height); //create new image canvas
	imagealphablending($imageFlip,false);
	imagesavealpha($imageFlip,true);
	$srcX=0;$srcY=0;$dstX=0;$dstY=0;$srcW=$width;$srcH=$height;
	if ($flipV){
		$srcX=$width-1; $srcW=-$width;
	} 
	if ($flipH){
		$srcY=$height-1; $srcH=-$height;
	}

	imagecopyresampled ($imageFlip, $imageOriginal, $dstX, $dstY, $srcX, $srcY, $width, $height, $srcW, $srcH);
	//rotate
	$rotatedImage = imagerotate($imageFlip, -$angle, 0);
	imagealphablending($rotatedImage,false);
	imagesavealpha($rotatedImage,true);
	//crop
	$croppedImage=imagecreatetruecolor($cropW,$cropH);
	imagealphablending($croppedImage,false);
	imagecopyresampled($croppedImage,$rotatedImage,0,0,$cropX,$cropY,$cropW,$cropH,$cropW,$cropH);
	imagesavealpha($croppedImage,true);
	if ($duplicate == false){
		unlink ($uploadDirectory.$physicalName.'.'.$type);
		if ($type == 'png'){
			imagepng($croppedImage,$uploadDirectory.$physicalName.'.png', 9); // save as png with maximum compression;
		} else {
			imagejpeg($croppedImage,$uploadDirectory.$physicalName.'.jpg',100); //save as jpeg with quality of 100	
		}	
		list($newWidth,$newHeight)=getimagesize($uploadDirectory.$physicalName.'.'.$type);
        DB::query(Query::Update('images')->pairs(array('width'=>$newWidth,'height'=>$newHeight))->eq('physicalName',$physicalName));
		deleteResizedImagesCache($physicalName);
		setError('Image Modified and saved over old one.',0);
		redirect('imagesView.php');
	} else { //create new image and copy attributes from old one
		$newPhysicalName = generateUniqueFileName($uploadDirectory,'.'.$type);
		if ($type == 'png'){
			imagepng($croppedImage,$uploadDirectory.$newPhysicalName.'.png', 9); // save as png with maximum compression;
		} else {
			imagejpeg($croppedImage,$uploadDirectory.$newPhysicalName.'.jpg',100); //save as jpeg with quality of 100	
		}	
		list($newWidth,$newHeight)=getimagesize($uploadDirectory.$newPhysicalName.'.'.$type);
		$description = mlString::Create($currentRecord->description);
		_d($description);
		$newDescription = mlString::Create()->setValues($description->getValues());
		
		_d($newDescription);		
		$label = $currentRecord->label.', modified';
		
		//addDS($description);
		
		$queryFields = array(
			'label'=>$label,
			'type'=>$type,
			'physicalName'=>$newPhysicalName,
			'width'=>$newWidth,
			'height'=>$newHeight,
			'useCount'=>0,
			'description'=>$newDescription->strID
		);
        $result = DB::query(Query::Insert('images')->pairs($queryFields));
		$newID = DB::insert_id();
		$newDescription->usedTable('images')->usedID($newID);
		$newDescription->save();
		setError('Image modified and saved as new image',0);
		redirect('imagesView.php');
	}
	
}



$form = formConstruct('Save Image');
$fieldset1='<fieldset class="col1 first">';
$form->find('fieldset.submit')->append(control_hidden('id', $id));
$form->find('fieldset.submit')->append(control_hidden('physicalName', $physicalName));
$form->find('fieldset.submit')->append(control_hidden('type', $type));
$form->find('fieldset.submit')->append(control_hidden('srcDir', conf('IMAGE_CONFORMED_DIRECTORY')));
$form->find('fieldset.submit')->append(control_hidden('angle', $angle));
$form->find('fieldset.submit')->append(control_hidden('cropX', $cropX));
$form->find('fieldset.submit')->append(control_hidden('cropY', $cropY));
$form->find('fieldset.submit')->append(control_hidden('cropW', $cropW));
$form->find('fieldset.submit')->append(control_hidden('cropH', $cropH));
$form->find('fieldset.submit')->append(control_hidden('flipH', $flipH));
$form->find('fieldset.submit')->append(control_hidden('flipV', $flipV));
$form->find('fieldset.submit')->append(field(label('Keep Original Image'),control_checkbox('duplicate', $duplicate)));



require_script('Scripts/imageEditor.js');
$webPage = webPageConstruct('Edit Image');
$webPage->find('h1')->before(constructMenu('imagesView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());

echo  outputWebPage($webPage);	