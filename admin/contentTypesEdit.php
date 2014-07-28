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

require_table('contenttypes');

$afterActions = array(
	array(
		'label'=>'stay here',
		'url'=>'',
		'default'=>true,
	),array(
		'label'=>'go back',
		'url'=>'contentTypesView.php'
	)
);

$perform=false;
// Init default form variables.

$templateHtmlSrc = $_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Content/';

if (isset($_GET['id'])){
	$id = $_GET['id'];
	$currentRecord =DB::row(Query::Select('contenttypes')->fields('typeID','label','comment','viewer','icon','useCount','formTemplate','listTemplate','scheme','rank','hidden')->eq( 'id', $id)->limit(1));
	_d($currentRecord);
	$typeID=$currentRecord->typeID;
	$typeID_O=$typeID;
	$viewer = $currentRecord->viewer;
	$label=$currentRecord->label;
	$comment=$currentRecord->comment;
	$icon=$currentRecord->icon;
	$rank = $currentRecord->rank;
	$hidden = $currentRecord->hidden;
	$formTemplate=$currentRecord->formTemplate;
	$listTemplate=$currentRecord->listTemplate;
	$scheme=$currentRecord->scheme;
	$useCount = $currentRecord->useCount;

    $tableName = 'contents_' . $typeID;
    require_table($tableName);
	countProvidersInPageScheme($scheme,-1);

    $templateViewNames = [''];
    $templateViewContents = [file_get_contents($templateHtmlSrc.ucfirst($typeID_O).'.twig')];

    $tplfiles = glob($templateHtmlSrc.ucfirst($typeID_O).'_*.twig');

    foreach ($tplfiles as $tplfile){
        $templateViewNames[] = str_replace('.twig','',str_replace($templateHtmlSrc.ucfirst($typeID_O).'_','',$tplfile));
        $templateViewContents[] = file_get_contents($tplfile);
    }

} 

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$id = $_POST['id'];
	$typeID=strtolower($_POST['typeID']);
	$typeID_O=$_POST['typeID_O'];
	$label=$_POST['label'];
	$comment=$_POST['comment'];
	$icon=$_POST['icon'];
	$viewer = $_POST['viewer'];
	$rank=$_POST['rank'];
	$hidden=isset($_POST['hidden'])?true:false;
	$formTemplate=$_POST['formTemplate'];
	$listTemplate=$_POST['listTemplate'];
	$scheme=$_POST['scheme'];

    $templateViewNames = $_POST['views_title'];
    $templateViewContents = $_POST['views_value'];

	$valid=true; // no detected errors as a start - success assumed

    if (strlen($typeID) < 3 || strlen($typeID) >50 || !allowedChars($typeID,conf('ALPHANUM_LOWER').'_') || !allowedChars(substr($typeID,0,1),conf('ALPHA_LOWER'))) {
		$valid = false;
		addFormError('typeID','Must be 3-50 letters long and can only start with a letter. No spaces.');
	}	
	
	if ($typeID!=$typeID_O){
		if (strtolower($typeID) != strtolower($typeID_O)){
			$exists = DB::val(Query::Select('contenttypes')->fields('id')->eq('typeID', $typeID)->limit(1));
			if ($exists) {
				$valid = false;
				addFormError('typeID','This Page Type already exists');
			}
		}
		if ($useCount > 0){
			$valid = false;
			addFormError('typeID','This Content Type is in use and cannot be renamed');
			$typeID = $typeID_O;
		}
	}
	
	if (strlen($label) < 1 || strlen($label) >50 || !allowedChars($label,conf('ALPHANUM').' -_,.')) {
		$valid = false;
		addFormError('label','Must be 1-50 charachters long.');
	}

    $templateViewNames[0] = '';
    $tvi = 0;
    foreach ($templateViewNames as $tvn) {
        if ($tvi > 0){
            if (!allowedChars($tvn,conf('ALPHANUM').'_')){
                $valid = false;
                addFormError('contentViews','View Names cannot contain special characters or spaces');
                break;
            }
        }
        $tvi++;
    }

    if ($valid && count(array_unique($templateViewNames))<count($templateViewNames)){
        $valid = false;
        addFormError('contentViews','There are duplicated names in the views');
    }
	
	if ($valid){ //if no errors, insert into database
		$queryFields = array(
			'typeID'=>$typeID,
			'label'=>$label,
			'comment'=>$comment,
			'viewer'=>$viewer,
			'icon'=>$icon,
			'formTemplate'=>$formTemplate,
			'listTemplate'=>$listTemplate,
			'scheme'=>$scheme,
			'rank'=>$rank,
			'hidden'=>$hidden
		);
        $result = DB::query(Query::Update('contenttypes')->pairs($queryFields)->eq('id',$id));
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
            if ($typeID_O != $typeID){
                DB::query('RENAME TABLE contents_'.$typeID_O.' TO contents_'.$typeID);
            }
			countProvidersInPageScheme($scheme,1);
			commitCounts();

            $oldViewers = glob($templateHtmlSrc.ucfirst($typeID_O).'_*.twig');
            $oldViewers[] = $templateHtmlSrc.ucfirst($typeID_O).'.twig';

            foreach ($oldViewers as $oldViewer) {
                unlink($oldViewer);
            }

            for ($i = 0; $i<count($templateViewNames); $i++){
                $filenameBase = $_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Content/'.ucFirst($typeID);
                if ($i==0){
                    $fileName = $filenameBase.'.twig';
                } else {
                    $fileName = $filenameBase.'_'.$templateViewNames[$i].'.twig';
                }
                file_put_contents($fileName,$templateViewContents[$i]);
            }
			
			setError('Content Type "'.$label.'" modified',0);
			
			if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $id, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}



$form = formConstruct('Edit Content Type',$afterActions);
$fieldset1='<fieldset class="col2 first thin">';
$fieldset1.='<h2 class="thin">Basic Properties</h2>';
$fieldset1.='</fieldset><fieldset class="col1 first">';
$fieldset1.=field(label('Content Type ID','letters only, no spaces'),control_textInput('typeID', $typeID),'typeID');
$fieldset1.=field(label('Label'),control_textInput('label', $label),'label');
$fieldset1.=field(label('Comment','optional'),control_textArea('comment', $comment,2),'comment');
$fieldset1.='</fieldset><fieldset class="col1">';
$fieldset1.=field(label('Default Viewer Page','optional'),control_pageSelect('viewer', $viewer),4);
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
$fieldset1.=field(label('View Templates'),control_contentViews('views', $templateViewNames,$templateViewContents),'contentViews');
$fieldset1.='</fieldset><fieldset class="col2 first">';
$fieldset1.='<h2 class="thin">Content Scheme</h2>';
$fieldset1.=control_pageScheme('scheme', $scheme);
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);
$form->find('fieldset.submit')->append(control_hidden('id', $id));
$form->find('fieldset.submit')->append(control_hidden('typeID_O', $typeID_O));


$webPage = webPageConstruct('Edit Content Type');
$webPage->find('h1')->before(constructMenu('contentTypesView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	
