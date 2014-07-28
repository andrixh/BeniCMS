<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/db.tables.inc.php');
require_once('Includes/errors.inc.php');
require_once('Includes/counter.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/form.inc.php');
require_once('Routines/validators.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/filesystem.php');
require_once('Routines/content.php');
require_once('Routines/videos.php');

$afterActions = array(
	array(
		'label'=>'stay here',
		'url'=>'',
		'default'=>true,
	),array(
		'label'=>'go back',
		'url'=>'videosView.php'
	)
);

require_table('videos');

// Init default form variables.

if (isset($_GET['id'])){
	$id = $_GET['id'];
	$query = Query::Select('videos')->fields('physicalName','videoID','service','label','description','thumbnail','ownThumbnail','useCount')->eq('id',$id)->limit(1);
	$currentRecord = DB::row($query);

	$autoUrl = '';
	$physicalName = $currentRecord->physicalName;
	$videoID = $currentRecord->videoID;
	$videoID_O = $videoID;
	$service = $currentRecord->service;
	$service_O = $service;
	$label = $currentRecord->label;
	$description=mlString::Create($currentRecord->description)->postName('description')->usedTable('videos');
	$thumbnail = $currentRecord->thumbnail;
	$thumbnail_O = $thumbnail;
	$ownThumbnail = $currentRecord->ownThumbnail;
	$ownThumbnail_O = $ownThumbnail;
}

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$id = $_POST['id'];

	$autoUrl = $_POST['autoUrl'];;
	$description->fromPost();
	$label = $_POST['label'];
	$service = $_POST['service'];
	$videoID = $_POST['videoID'];
	$useCount = 0;
	$thumbnail = $_POST['thumbnail'];


	//$ownThumbnail =

	$valid=true; // no detected errors as a start - success assumed

	if ($autoUrl != ''){
		$autoData = decodeVideoUrl($autoUrl);
		if ($autoData){
			$service = $autoData[0];
			$videoID = $autoData[1];
		} else {
			$valid = false;
			addFormError('autoUrl','Could not understand this url');
		}
	}

	if (!in_array($service,array('youtube','vimeo','dailymotion'))) {
		$valid = false;
		addFormError('service','Unknown Service');
	} else {
		if ($service == 'youtube'){
			$videoUrl = 'http://www.youtube.com/watch?v='.$videoID;
		} elseif ($service == 'vimeo'){
			$videoUrl = 'http://vimeo.com/'.$videoID;
		} elseif ($service == 'dailymotion'){
			$videoUrl = 'http://www.dailymotion.com/video/'.$videoID;
		}

		$headers = get_headers($videoUrl);
		if (!strpos($headers[0], '200')){
			addFormError('videoID','This Video ID does not exist in the service');
		}
	}

	$exists = DB::val(Query::Select('videos')->fields('ID')->eq('videoID', $videoID)->limit(1));
	if ($exists && $videoID != $videoID_O){
		$valid = false;
		addFormError('videoID','This Video is already in the system');
	}


	$details = getVideoData($service,$videoID);
	if (strlen($label) < 1 && $autoData){
		$label = $details['title'];
	}


	if (strlen($label) < 1 || strlen($label)>255){
		$valid = false;
		addFormError('label','Label must be between 1 and 255 letters long.');
	}

	if ($valid){ //if no errors, insert into database
		if ($thumbnail != '' && $thumbnail!='[]'){
			$thumbnailData = json_decode($thumbnail);
			$thumbPname = $thumbnailData[0]->physicalName;
		}


		$sourceChanged = ($service!=$service_O || $videoID!=$videoID_O);
		$thumbChanged = ($thumbnail!=$thumbnail_O);
		$deleteOldThumb = ($thumbChanged && $ownThumbnail_O && !($thumbnail == '' || $thumbnail == '[]'));
		if (!$sourceChanged && $ownThumbnail_O && ($thumbnail == '' || $thumbnail == '[]')){ //own thumbnail still in effect
			$deleteOldThumb = false;
			$thumbChanged = false;
			$thumbnail = $thumbnail_O;
			$ownThumbnail = $ownThumbnail_O;
		}

		//prepare images


		if ($deleteOldThumb) {
			$thumbnailData = json_decode($thumbnail_O);

			$thumbPname = $thumbnailData[0]->physicalName;
			$thumbData = DB::row(Query::Select('images')->fields('useCount','description','physicalName')->eq('physicalName',$thumbPname));
			if ($thumbData->useCount<=1){
				$desc = mlString::Create($thumbData->description)->delete();
				DB::query(Query::Delete('images')->eq('physicalName', $thumbData->physicalName));
				unlink($_SERVER['DOCUMENT_ROOT'].conf('IMAGE_CONFORMED_DIRECTORY').$thumbData->physicalName.'.jpg');
				$resizedFiles = glob($_SERVER['DOCUMENT_ROOT'].conf('IMAGE_RESIZED_DIRECTORY').$thumbData->physicalName.'*.*');
				array_map("unlink", $resizedFiles);
				$ownThumbnail = false;
			}
		} else if ($thumbChanged) {
			countGallery($thumbnail_O,-1);
		}


		if ($thumbnail == '' || $thumbnail=='[]'){
			if ($service == 'youtube'){
				$imageUrl = 'http://img.youtube.com/vi/'.$videoID.'/0.jpg';
			} else if ($service == 'vimeo'){
				$jsonurl = 'http://vimeo.com/api/v2/video/'.$videoID.'.json';
				$jsonData = json_decode(file_get_contents($jsonurl));
				$imageUrl = $jsonData[0]->thumbnail_large;
			} elseif ($service == 'dailymotion'){
				$imageUrl = 'http://www.dailymotion.com/thumbnail/video/'.$videoID;
			}
			$thumbnail = '[{"resourceType":"image","physicalName":"'.$physicalName.'","type":"jpg"}]';
			file_put_contents($_SERVER['DOCUMENT_ROOT'].conf('IMAGE_CONFORMED_DIRECTORY').$physicalName.'.jpg',file_get_contents($imageUrl));
			list($w,$h)= getimagesize($_SERVER['DOCUMENT_ROOT'].conf('IMAGE_CONFORMED_DIRECTORY').$physicalName.'.jpg');
			$imageRecord = array(
				'physicalName'=>$physicalName,
				'type'=>'jpg',
				'label'=>$label.' video thumbnail',
				'useCount'=>0,
				'description'=>'',
				'width'=>$w,
				'height'=>$h
			);
			$query = Query::Insert('images')->pairs($imageRecord);
			DB::query($query);
			$ownThumbnail = true;
		}


		$queryFields = array();
		$queryFields['physicalName']=$physicalName;
		$queryFields['videoID']=$videoID;
		$queryFields['service']=$service;
		$queryFields['label']=$label;
		$queryFields['description']=$description->strID;
		$queryFields['thumbnail']=$thumbnail;
		$queryFields['ownThumbnail']=$ownThumbnail;
		$queryFields['useCount']=0;

		
		$query = Query::Update('videos')->pairs($queryFields)->eq('id',$id);_d($query);
		$result = DB::query($query);
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			$description->save();


		if ($thumbChanged){
			countGallery($thumbnail,1);
		}
			setError('Video modified',0);
			commitCounts();
			_d('clearing form values');
			if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $insertID, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}




$form = formConstruct('Edit Video',$afterActions);
$fieldset1='<fieldset class="col1 first"><h2>Video Source</h2>';
$fieldset1.=field(label('Paste Url','auto-detect'),control_textArea('autoUrl', $autoUrl,3),'autoUrl');
$fieldset1.=field(label('Service'),control_select('service', $service, array('youtube'=>'YouTube','vimeo'=>'Vimeo','dailymotion'=>'DailyMotion')),'service');
$fieldset1.=field(label('VideoID','paste from url'),control_textInput('videoID', $videoID),'videoID');
$fieldset1.=field(label('Label'),control_textInput('label', $label),'label');

$fieldset1.='</fieldset><fieldset class="col1"><h2>Representation</h2><p>Use a custom image, or leave empty to retrieve image from the service.</p>';
$fieldset1.=field(label('Thumbnail Image'),control_galleryField('thumbnail', array($thumbnail), false, true, true),'thumbnail');
$fieldset1.=field(label('Description'),control_mlTextInput('description', $description->getValues(),1),'description');
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);
$form->find('fieldset.submit')->append(control_hidden('id', $id));

$pageTitle = 'Edit Video';
//if (isset($_GET['id'])){ $pageTitle = 'Duplicate Static String';}


$webPage = webPageConstruct($pageTitle);
$webPage->find('h1')->before(constructMenu('videosView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	