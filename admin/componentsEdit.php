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

if (!isset($_GET['type']) && !isset($_POST['type'])){
	setError('Cannot find type of component!',2);
	redirect($_SERVER['HTTP_REFERER']);
} else {
	$type = $_GET['type'];
	if (isset($_POST['type'])){
		$type=$_POST['type'];
	}
}
$tableName = 'components_'.$type;
$scheme = json_decode(DB::val(Query::Select('componenttypes')->fields('scheme')->eq('typeID', $type)->limit(1)));

require_table($tableName);

$afterActions = array(
	array(
		'label'=>'stay here',
		'url'=>'',
		'default'=>true,
	),array(
		'label'=>'go back',
		'url'=>'componentsView.php?type='.$type,
	)
);
$typeData = DB::row(Query::Select('componenttypes')->fields('scheme','label','formTemplate')->eq('typeID',$type)->limit(1));
_d($typeData);
$typeScheme = json_decode($typeData->scheme);
$typeFormTemplate = $typeData->formTemplate;

$perform=false;
// Init default form variables.
if (isset($_GET['id'])){
	$id = $_GET['id'];
	$currentRecord =DB::row(Query::Select($tableName)->eq('id', $id)->limit(1),DB::ASSOC);

	$componentID=$currentRecord['componentID'];
	$componentID_O = $componentID;
    $custom = file_exists($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'_'.$componentID.'.twig');
    if ($custom) {
	    $templateHtml=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'_'.$componentID.'.twig');
    } else {
        $templateHtml = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'.twig');
    }
	$content = array();
	foreach ($typeScheme as $typeField){
		if (array_search($typeField->type, getMlTypes())){
			$content[$typeField->name] = mlString::Create($currentRecord[$typeField->name])->postName('content_'.$typeField->name);
		} else {
			$content[$typeField->name] = $currentRecord[$typeField->name];
		}
	}
	_d($content,'estivated content');
	countContentResources($content,$typeScheme,-1);
	_d($_counter);
}

//perform goes here
if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$id = $_POST['id'];
	$componentID=$_POST['componentID'];
	$componentID_O=$_POST['componentID_O'];
	$custom = isset($_POST['custom']);
    if ($custom){
        $templateHtml=$_POST['templateHtml'];
    } else {
        $templateHtml = file_get_contents(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'.twig'));
    }
	$content = contentFromPost($content,$typeScheme);
	
			
	$valid = contentValidate($content,$typeScheme);
	
	if (strlen($componentID) < 1 || strlen($componentID) > 255 || !allowedChars($componentID,conf('ALPHANUM').'-_')){
		$valid = false;
		addFormError(1,'Component ID must be 1-255 charachters long, and cannot contain special characters.');
	}
	if ($componentID != $componentID_O){
		$exists = DB::val(Query::Select($tableName)->fields('id')->eq('componentID', $componentID)->limit(1));
		if ($exists) {
			$valid = false;
			addFormError(1,'This Component ID already exists');
		}
	}
	
	
	if ($valid){
		countContentResources($content,$typeScheme,1);
		
		$queryFields = array(
			'componentID'=>$componentID,
		);
		foreach ($typeScheme as $typeField){
			$queryFields[$typeField->name] = $content[$typeField->name];
		}

        $result = DB::query(Query::Update($tableName)->pairs($queryFields)->eq('id',$id));
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {

            if (file_exists($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'_'.$componentID_O.'.twig')){
                unlink ($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'_'.$componentID_O.'.twig');
            }
            if (file_exists($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'_'.$componentID.'.twig')){
                unlink ($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'_'.$componentID.'.twig');
            }

            if ($custom){
                file_put_contents($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($type).'_'.$componentID.'.twig',$templateHtml);
            }
			$componentID_O = $componentID;
			$mlstrings = pageContentExtractMlStrings($content,$typeScheme);
			foreach ($mlstrings as $mlstring) {
				$mlstring->usedTable($tableName)->usedID($id)->save();
			}
			commitCounts();
			setError('Component '.ucfirst($typeData->label).' "'.$componentID.'" modified',0);
			if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $id, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}

require_script('Scripts/Controls/templateEnabler.js');

$form = formConstruct('Modify '.ucfirst($typeData->label).' Component',$afterActions);
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
$form->find('fieldset.submit')->append(control_hidden('id', $id));
$form->find('fieldset.submit')->append(control_hidden('componentID_O', $componentID_O));
_u();
$fieldset2 = contentFields($content, $typeScheme, $typeFormTemplate);

$form->find('fieldset.submit')->before($fieldset2);

$webPage = webPageConstruct('Modify '.ucfirst($typeData->label).' Component');
$webPage->find('h1')->before(constructMenu('componentsView.php?type='.$type));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	

