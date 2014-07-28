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
		'label'=>'add another component type',
		'url'=>'',
	),
	array(
		'label'=>'edit new component type',
		'url'=>'componentTypesEdit.php?id=%id%',
		'default'=>true,
	),array(
		'label'=>'go back',
		'url'=>'componentTypesView.php',
	)
);


$perform=false;
// Init default form variables.
$typeID='';
$label='';
$comment='';
$icon = '';
$rank = 20;
$hidden = false;
$formTemplate='';
$listTemplate='';
$scheme='';
$useCount = 0;

$templateHtmlSrc = $_SERVER['DOCUMENT_ROOT'].'/admin/BaseTemplates/.componentTypeTemplate.twig';
$templatePhpSrc = $_SERVER['DOCUMENT_ROOT'].'/admin/BaseTemplates/.componentTypeTemplate.php';

$templateHtml = file_exists($templateHtmlSrc)?file_get_contents($templateHtmlSrc):'';
$templatePhp = file_exists($templatePhpSrc)?file_get_contents($templatePhpSrc):'';

$id = -1;

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$typeID=strtolower($_POST['typeID']);
	$label=$_POST['label'];
	$comment=$_POST['comment'];
	$icon=$_POST['icon'];
	$rank=$_POST['rank'];
	$hidden=isset($_POST['hidden'])?true:false;
	$formTemplate=$_POST['formTemplate'];
	$listTemplate=$_POST['listTemplate'];
	$scheme=$_POST['scheme'];
	$useCount = 0;

    $templateHtml = roleCheck(ROLE_ADMIN)?$_POST['templateHtml']:$templateHtml;
    $templatePhp = roleCheck(ROLE_DEV)?$_POST['templatePhp']:$templatePhp;

	$valid=true; // no detected errors as a start - success assumed

    if (strlen($typeID) < 3 || strlen($typeID) >50 || !allowedChars($typeID,conf('ALPHANUM_LOWER').'_') || !allowedChars(substr($typeID,0,1),conf('ALPHA_LOWER'))) {
        $valid = false;
        addFormError('typeID','Must be 3-50 letters long and can only start with a letter. No spaces.');
    } else {
        $exists = DB::val(Query::Select('componenttypes')->fields('id')->eq('typeID', ucfirst($typeID)));
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

	if (strlen($label) < 1 || strlen($label) >50 || !allowedChars($label,conf('ALPHANUM').' -_,.')) {
		$valid = false;
		addFormError('label','Must be 1-50 charachters long.');
	}
	
	if (!valid_int($rank) || $rank <0) {
		$valid = false;
		addFormError('rank', 'Rank must be a positive integer number.');
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
			'useCount'=>$useCount,
			'rank'=>$rank,
			'hidden'=>$hidden
		);
        $result = DB::query(Query::Insert('componenttypes')->pairs($queryFields));
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			$insertID = DB::insert_id();

			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Component/'.ucFirst($typeID).'.twig', $templateHtml);
			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/ContentClasses/Component/'.ucFirst($typeID).'.php', $templatePhp);

			setError('Component Type "'.$label.'" created',0);
			countProvidersInPageScheme($scheme,1);
			commitCounts();
			if (isset($_POST['afterAction']) && $_POST['afterAction']==''){
				$typeID='';
				$label='';
				$comment='';
				$icon='';
				$formTemplate='';
				$listTemplate='';
				$scheme='';
				$rank = 20;
				$hidden = false;
				$useCount = 0;
			}else if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $insertID, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}

require_script('Scripts/Controls/idNamer.js');

$form = formConstruct('Add Component Type',$afterActions);
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


$webPage = webPageConstruct('Add New Component Type');
$webPage->find('h1')->before(constructMenu('componentTypesView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	
