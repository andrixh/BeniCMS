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
require_once('Routines/filesystem.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/content.php');
require_once('Routines/selectProviders.php');


if (!(isset($_GET['type']) || isset($_POST['type']))){ //in case no content type is selected
	$contentTypes = DB::get(Query::Select('contenttypes')->fields('typeID','label','icon','comment','rank','hidden')->asc('hidden')->asc('rank')->asc('label'));
	$pTypes = phpQuery::newDocument('<ul class="pageTypes"></ul>');
	if ($contentTypes){
		foreach ($contentTypes as $contentType){
			$pLink = phpQuery::newDocument('<li><a></a></li>');
			$pLink->find('a')->attr('href','?type='.$contentType->typeID);
			$pLink->find('a')->append('<img src="Gfx/PageTypes/'.$contentType->icon.'.png"/>');
			$pLink->find('a')->append('<span>'.$contentType->label.'</span>');
			if ($contentType->comment != '') {
				$pLink->find('a')->append('<em>'.$contentType->comment.'</em>');
			}
			if ($contentType->hidden == 1){
				$pLink->find('a')->addClass('shy');
			}
			$pTypes->find('ul')->append($pLink);
		}
	} else {
		setError('No Content types defined. To proceed, you must first define at least one content type.',0,false);
		$pTypes->find('ul')->append('<li><a href="contentTypesAdd.php"><img src="Gfx/PageTypes/Orb plus.png"><span>Add New Content Type</span></a></li>');
	}
	
	require_style('Css/pages.css');
	require_style('Css/admin.css');
	$webPage = webPageConstruct('Select Content Type');
	$webPage->find('h1')->before(constructMenu('contentsAdd.php'));
	$webPage->find('h1')->after($pTypes);
	$webPage->find('h1')->after(generateMessageBar());
	echo  outputWebPage($webPage);	
	
	die();	
}
//////////////////////////////////////////
$type=$_GET['type'];
if (isset($_POST['type'])){
	$type=$_POST['type'];
}
$tableName = 'contents_'.$type;
$scheme = json_decode(DB::val(Query::Select('contenttypes')->fields('scheme')->eq('typeID', $type)->limit(1)));
require_table($tableName);

$afterActions = array(
	array(
		'label'=>'add another Content item',
		'url'=>'contentsAdd.php?type='.$type,
	),
	array(
		'label'=>'edit new Content',
		'url'=>'contentsEdit.php?type='.$type.'&id=%id%',
	),array(
		'label'=>'go back',
		'url'=>'contentsView.php?type='.$type,
		'default'=>true,
	)
);


$perform=false;
// Init default form variables.
$contentID='';

$typeData = DB::row(Query::Select('contenttypes')->fields('scheme','label','formTemplate')->eq('typeID', $type)->limit(1));
$typeScheme = json_decode($typeData->scheme);
$typeFormTemplate = $typeData->formTemplate;
_d($typeData);
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/Templates/Contents/'.$type.'.content.html')){
	$templateHtml=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/Templates/Contents/'.$type.'.content.html');
}
$content=contentCreate($typeScheme);

$id = -1;

//perform goes here
if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
    $contentID = strtolower($_POST['contentID']);
	$content = contentFromPost($content,$typeScheme);
		
	$valid = contentValidate($content,$typeScheme);

    $contentID = str_replace(' ','_',$contentID);
    if (strlen($contentID) < 3 || strlen($contentID) >255 || !allowedChars($contentID,conf('ALPHANUM_LOWER').'_') || !allowedChars(substr($contentID,0,1),conf('ALPHA_LOWER'))) {
        $valid = false;
        addFormError('contentID','Must be 3-50 letters long and can only start with a letter. No spaces.');
    }

    $exists = DB::row(Query::Select($tableName)->fields('id')->eq('contentID', $contentID));
    if ($exists) {
        $valid = false;
        addFormError('contentID','This Page ID already exists');
    }

	if ($valid){
		countContentResources($content,$typeScheme,1);
		setCount('contenttype', $type, 1);
		
		$queryFields = array(
			'contentID'=>$contentID,
			'useCount'=>0
		);
		
      foreach ($typeScheme as $typeField){
         $queryFields[$typeField->name] = $content[$typeField->name];
      }

        $result = DB::query(Query::Insert($tableName)->pairs($queryFields));
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			$insertID = DB::insert_id();
			$mlstrings = pageContentExtractMlStrings($content,$typeScheme);
			foreach ($mlstrings as $mlstring) {
				$mlstring->usedTable($tableName)->usedID($insertID)->save();
			}
			commitCounts();
			setError(ucfirst($typeData->label).' content "'.$contentID.'" created.',0);
			//setError(ucfirst($type).' created',0);
			if (isset($_POST['afterAction']) && $_POST['afterAction']==''){
				$contentID='';
			} else if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $insertID, $_POST['afterAction']));
			}	
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}


$form = formConstruct('Add '.$typeData->label.' Content',$afterActions);
_gc('Hardcoded Controls');

$fieldset1='<fieldset class="col1 first">';
$fieldset1.=field(label('Content ID','permalink - unique'),control_textInput('contentID', $contentID),'contentID');
$fieldset1.=control_hidden('type', $type);
$fieldset1.='</fieldset>';
//$fieldset1= control_hidden('type', $type);

$form->find('fieldset.submit')->before($fieldset1);

_u();

$fieldset2 = contentFields($content, $typeScheme, $typeFormTemplate);

$form->find('fieldset.submit')->before($fieldset2);

$webPage = webPageConstruct('Add '.$typeData->label.' Content');
$webPage->find('h1')->before(constructMenu('contentsAdd.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);



