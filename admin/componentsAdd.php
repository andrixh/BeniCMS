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


if (!(isset($_GET['type']) || isset($_POST['type']))){ //in case no component type is selected
	$componentTypes = DB::get(Query::Select('componenttypes')->fields('typeID','label','icon','comment','rank','hidden')->asc('hidden')->asc('rank')->asc('label'));
	$pTypes = phpQuery::newDocument('<ul class="pageTypes"></ul>');
	if ($componentTypes){
		foreach ($componentTypes as $componentType){
			$pLink = phpQuery::newDocument('<li><a></a></li>');
			$pLink->find('a')->attr('href','?type='.$componentType->typeID);
			$pLink->find('a')->append('<img src="Gfx/PageTypes/'.$componentType->icon.'.png"/>');
			$pLink->find('a')->append('<span>'.$componentType->label.'</span>');
			if ($componentType->comment != '') {
				$pLink->find('a')->append('<em>'.$componentType->comment.'</em>');
			}
			if ($componentType->hidden == 1){
				$pLink->find('a')->addClass('shy');
			}
			$pTypes->find('ul')->append($pLink);
		}
	} else {
		setError('No Component types defined. To proceed, you must first define at least one component type.',0,false);
		$pTypes->find('ul')->append('<li><a href="componentTypesAdd.php"><img src="Gfx/PageTypes/Orb plus.png"><span>Add New Component Type</span></a></li>');
	}
	
	require_style('Css/pages.css');
	require_style('Css/admin.css');
	$webPage = webPageConstruct('Select Component Type');
	$webPage->find('h1')->before(constructMenu('componentsAdd.php'));
	$webPage->find('h1')->after($pTypes);
	$webPage->find('h1')->after(generateMessageBar());
	echo  outputWebPage($webPage);	
	
	die();	
}
/////////////////////////////////
$type=$_GET['type'];
if (isset($_POST['type'])){
	$type=$_POST['type'];
}
$tableName = 'components_'.$type;
$scheme = json_decode(DB::val(Query::Select('componenttypes')->fields('scheme')->eq('typeID',$type)->limit(1)));

require_table($tableName);

$afterActions = array(
	array(
		'label'=>'add another Component Instance',
		'url'=>'componentsAdd.php?type='.$type,
	),
	array(
		'label'=>'edit new Component Instance',
		'url'=>'componentsEdit.php?type='.$type.'&id=%id%',
	),array(
		'label'=>'go back',
		'url'=>'componentsView.php?type='.$type,
		'default'=>true,
	)
);


$perform=false;
// Init default form variables.
$componentID='';
$type=$_GET['type'];
$typeData = DB::row(Query::Select('componenttypes')->fields('scheme','label','formTemplate')->eq('typeID', $type)->limit(1));
$typeScheme = json_decode($typeData->scheme);
$typeFormTemplate = $typeData->formTemplate;
_d($typeData);
$templateHtml=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'.twig');
$content=contentCreate($typeScheme);

$id = -1;

//perform goes here
if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$componentID = $_POST['componentID'];
    $custom = isset($_POST['custom']);
    if ($custom) {
        $templateHtml = $_POST['templateHtml'];
    }

	$content = contentFromPost($content,$typeScheme);

	$valid = contentValidate($content,$typeScheme);

	if (strlen($componentID) < 1 || strlen($componentID) > 255 || !allowedChars($componentID,conf('ALPHANUM').'-_')){
		$valid = false;
		addFormError(1,'Page ID must be 1-255 charachters long, and cannot contain special characters.');
	}
	
	$exists = DB::val(Query::Select($tableName)->fields('id')->eq('componentID', $componentID)->limit(1));
	if ($exists) {
		$valid = false;
		addFormError(1,'This Component ID already exists');
	}
	
	if ($valid){
		countContentResources($content,$typeScheme,1);
		setCount('componentType', $type, 1);
		
		$queryFields = array(
			'componentID'=>$componentID,
			'useCount'=>0
		);
		foreach ($typeScheme as $typeField){
			$queryFields[$typeField->name] = $content[$typeField->name];
		}
        $result = DB::query(Query::Insert($tableName)->pairs($queryFields));
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
            if ($custom) {
                file_put_contents($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'_'.$componentID.'.twig',$templateHtml);
            }

			$insertID = DB::insert_id();
			$mlstrings = pageContentExtractMlStrings($content,$typeScheme);
			foreach ($mlstrings as $mlstring) {
				$mlstring->usedTable($tableName)->usedID($insertID)->save();
			}
			commitCounts();
			setError('Component '.$typeData->label.' "'.$componentID.'" created',0);
			if (isset($_POST['afterAction']) && $_POST['afterAction']==''){
				$componentID='';
				$type=$type;
                $custom = false;
				$useCount=0;
                $templateHtml=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'.twig');
			} else if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $insertID, $_POST['afterAction']));
			}	
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}

require_script('Scripts/Controls/templateEnabler.js');

$form = formConstruct('Add '.$typeData->label.' Component',$afterActions);
_gc('Hardcoded Controls');
$fieldset1='<fieldset class="col1 first">';
$fieldset1.=field(label('Component ID','unique'),control_textInput('componentID', $componentID),1);
$fieldset1.=control_hidden('type', $type);
$fieldset1.='</fieldset>';
$fieldset1.='<fieldset class="col2 first">';
$fieldset1.=field(label('Custom HTML Template'),control_code('templateHtml', $templateHtml, 'xml'),'templateHtml');
$fieldset1.=field(label_checkbox('Enable Custom Template'),control_checkbox('custom', $custom));
$fieldset1.='</fieldset>';
$fieldset1.='<fieldset class="col2 first thin">';
$fieldset1.='<h2 class="">Content</h2>';
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);

_u();

$fieldset2 = contentFields($content, $typeScheme, $typeFormTemplate);

$form->find('fieldset.submit')->before($fieldset2);

$webPage = webPageConstruct('Add '.$typeData->label.' Component');
$webPage->find('h1')->before(constructMenu('componentsAdd.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	

