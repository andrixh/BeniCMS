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
require_once('Routines/selectProviders.php');

require_table('componenttypes');

$afterActions = array(
	array(
		'label'=>'stay here',
		'url'=>'',
		'default'=>true,
	),array(
		'label'=>'go back',
		'url'=>'componentTypesView.php'
	)
);

$perform=false;
// Init default form variables.

if (isset($_GET['id'])){
	$id = $_GET['id'];
	$currentRecord = DB::row(Query::Select('componenttypes')->fields('typeID','label','comment','icon','formTemplate','listTemplate','useCount','scheme','rank','hidden')->eq('id', $id));
	_d($currentRecord);
	$typeID=$currentRecord->typeID;
	$typeID_O=$typeID;
	$label=$currentRecord->label;
	$comment=$currentRecord->comment;
	$icon=$currentRecord->icon;
	$rank = $currentRecord->rank;
	$hidden = $currentRecord->hidden;
	$formTemplate=$currentRecord->formTemplate;
	$listTemplate=$currentRecord->listTemplate;
	$scheme=$currentRecord->scheme;
	$useCount = $currentRecord->useCount;

    $templateHtml = '';
    $templatePhp = '';
    $templateHtmlSrc = $_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucfirst($typeID).'.twig';
    $templatePhpSrc = $_SERVER['DOCUMENT_ROOT'].'/ContentClasses/Component/'.ucFirst($typeID).'.php';
    if ($templateHtmlSrc){
        $templateHtml = file_get_contents($templateHtmlSrc);
    }
    if (file_exists($templatePhpSrc)){
        $templatePhp = file_get_contents($templatePhpSrc);
    }
	countProvidersInPageScheme($scheme,-1);
} 

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$id = $_POST['id'];
	$typeID=strtolower($_POST['typeID']);
	$typeID_O=$_POST['typeID_O'];
	$label=$_POST['label'];
	$comment=$_POST['comment'];
	$icon=$_POST['icon'];
	$rank=$_POST['rank'];
	$hidden=isset($_POST['hidden'])?true:false;
	$formTemplate=$_POST['formTemplate'];
	$listTemplate=$_POST['listTemplate'];
	$scheme=$_POST['scheme'];
	
	$templateHtml = $_POST['templateHtml'];
	$templatePhp = $_POST['templatePhp'];
	$valid=true; // no detected errors as a start - success assumed

    if (strlen($typeID) < 3 || strlen($typeID) >50 || !allowedChars($typeID,conf('ALPHANUM_LOWER').'_') || !allowedChars(substr($typeID,0,1),conf('ALPHA_LOWER'))) {
		$valid = false;
		addFormError('typeID','Must be 3-50 letters long and can only start with a letter. No spaces.');
	}	
	
	if ($typeID!=$typeID_O){
		if (strtolower($typeID) != strtolower($typeID_O)){
			$exists = DB::val(Query::Select('componenttypes')->fields('id')->eq('typeID', $typeID));
            if (!$exists){
                if (file_exists($_SERVER['DOCUMENT_ROOT'].'/ContentClasses/Component/'.ucFirst($typeID).'.php')){
                    $exists = true;
                }
            }
			if ($exists) {
				$valid = false;
				addFormError('typeID','This Component Type already exists');
			}
		}
		if ($useCount > 0){
			$valid = false;
			addFormError('typeID','This Component Type is in use and cannot be renamed');
			$typeID = $typeID_O;
		}
	}
	
	if (strlen($label) < 1 || strlen($label) >50 || !allowedChars($label,conf('ALPHANUM').' -_,.')) {
		$valid = false;
		addFormError('label','Must be 1-50 charachters long.');
	}
	
	if ($valid){ //if no errors, insert into database
		$queryFields = array(
			'typeID'=>$typeID,
			'label'=>$label,
			'comment'=>$comment,
			'icon'=>$icon,
			'formTemplate'=>$formTemplate,
			'listTemplate'=>$listTemplate,
			'scheme'=>$scheme,
			'rank'=>$rank,
			'hidden'=>$hidden
		);
        $result = DB::query(Query::Update('componenttypes')->pairs($queryFields)->id($id));
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			if ($typeID!=$typeID_O){
                rename($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucfirst($typeID_O).'.twig',$_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucfirst($typeID).'.twig');
                rename($_SERVER['DOCUMENT_ROOT'].'/ContentClasses/Component/'.ucFirst($typeID_O).'.php',$_SERVER['DOCUMENT_ROOT'].'/ContentClasses/Component/'.ucFirst($typeID).'.php');

                //rename custom templates
                $customTemplates = glob($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucfirst($typeID_O).'_*.twig');
                foreach ($customTemplates as $customTemplate){
                    rename ($customTemplate,str_replace('/Component/'.ucfirst($typeID_O),'/Component/'.ucfirst($typeID),$customTemplate));
                }

                DB::query('RENAME TABLE components_'.$typeID_O.' TO components_'.$typeID);
			}

            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucfirst($typeID).'.twig', $templateHtml);
            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/ContentClasses/Component/'.ucFirst($typeID).'.php', $templatePhp);

			countProvidersInPageScheme($scheme,1);
			commitCounts();
			
			setError('Component Type "'.$label.'" modified',0);
			
			if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $id, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}

require_script('Scripts/Controls/idNamer.js');

$form = formConstruct('Edit Component Type',$afterActions);
$fieldset1='<fieldset class="col2 first thin">';
$fieldset1.='<h2 class="thin">Basic Properties</h2>';
$fieldset1.='</fieldset><fieldset class="col1 first">';
$fieldset1.=field(label('Component Type ID','letters only, no spaces'),control_textInput('typeID', $typeID),'typeID');
$fieldset1.=field(label('Label'),control_textInput('label', $label),'label');
$fieldset1.=field(label('Comment','optional'),control_textArea('comment', $comment,2),'comment');
$fieldset1.='</fieldset><fieldset class="col1">';
$fieldset1.=field(label('Icon'),control_selectImage('icon', $icon, provide_files('Gfx/PageTypes', 'png')),4);
$fieldset1.=field(label('Rank','(lowest appears first)'),control_textInput('rank', $rank),'rank');
$fieldset1.=field(label_checkbox('Hidden'),control_checkbox('hidden', $hidden));
$fieldset1.='</fieldset><fieldset class="col2 first thin">';
$fieldset1.='<h2 class="thin">Templates</h2>';
$fieldset1.='</fieldset><fieldset class="col1 first">';
$fieldset1.=field(label('Form Template','controls with <control name="name" />'),control_code('formTemplate', $formTemplate, 'xml'),4);
$fieldset1.='</fieldset><fieldset class="col1">';
$fieldset1.=field(label('List Template'),control_code('listTemplate', $listTemplate, 'xml'),4);
$fieldset1.='</fieldset><fieldset class="col2 first">';
$fieldset1.=field(label('Default HTML Template'),control_code('templateHtml', $templateHtml, 'twig'),'templateHtml');
$fieldset1.=field(label('PHP code'),control_code('templatePhp', $templatePhp, 'php'),'templatePhp');
$fieldset1.='</fieldset><fieldset class="col2 first">';
$fieldset1.='<h2 class="thin">Content Scheme</h2>';
$fieldset1.=control_pageScheme('scheme', $scheme);
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);
$form->find('fieldset.submit')->append(control_hidden('id', $id));
$form->find('fieldset.submit')->append(control_hidden('typeID_O', $typeID_O));


$webPage = webPageConstruct('Edit Component Type');
$webPage->find('h1')->before(constructMenu('componentTypesView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	
