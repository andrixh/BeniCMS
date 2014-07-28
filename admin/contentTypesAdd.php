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
		'label'=>'add another content type',
		'url'=>'',
	),
	array(
		'label'=>'edit new content type',
		'url'=>'contentTypesEdit.php?id=%id%',
		'default'=>true,
	),array(
		'label'=>'go back',
		'url'=>'contentTypesView.php',
	)
);


$perform=false;
// Init default form variables.
$typeID='';
$label='';
$comment='';
$icon = '';
$viewer = '';
$rank = 20;
$hidden = false;
$formTemplate='';
$listTemplate='';
$scheme='';
$useCount = 0;

$templateHtml='';
$templatePhp='';

$templateHtmlSrc = $_SERVER['DOCUMENT_ROOT'].'/admin/BaseTemplates/.contentTypeTemplate.twig';

$templateViewNames = [''];
$templateViewContents = [file_exists($templateHtmlSrc)?file_get_contents($templateHtmlSrc):''];

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

    $templateViewNames = $_POST['views_title'];
    $templateViewContents = $_POST['views_value'];

	$valid=true; // no detected errors as a start - success assumed

    if (strlen($typeID) < 3 || strlen($typeID) >50 || !allowedChars($typeID,conf('ALPHANUM_LOWER').'_') || !allowedChars(substr($typeID,0,1),conf('ALPHA_LOWER'))) {
		$valid = false;
		addFormError('typeID','Must be 3-50 letters long and can only start with a letter. No spaces.');
	}	
	
	$exists = DB::val(Query::Select('contenttypes')->fields('id')->eq('typeID', $typeID)->limit(1));
	if ($exists) {
		$valid = false;
		addFormError('typeID','This Content Type already exists');
	}
	
	if (strlen($label) < 1 || strlen($label) >50 || !allowedChars($label,conf('ALPHANUM').' -_,.')) {
		$valid = false;
		addFormError('label','Must be 1-50 charachters long.');
	}
	
	if (!valid_int($rank) || $rank <0) {
		$valid = false;
		addFormError('rank', 'Rank must be a positive integer number.');
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
			'useCount'=>$useCount,
			'rank'=>$rank,
			'hidden'=>$hidden
		);
		$result = DB::query(Query::Insert('contenttypes')->pairs($queryFields));
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			$insertID = DB::insert_id();
			setError('Content Type "'.$label.'" created',0);
			countProvidersInPageScheme($scheme,1);
			commitCounts();

            $tableName = 'contents_' . $typeID;
            $scheme = json_decode(DB::val(Query::Select('contenttypes')->fields('scheme')->eq('typeID', $typeID)->limit(1)));
            $tableScheme = contentDef($scheme);
            $tableScheme['permalink'] = 'VARCHAR(255)';
            $tableScheme['useCount'] = 'INT(10)';
            require_table($tableName);


            for ($i = 0; $i<count($templateViewNames); $i++){
                $filenameBase = $_SERVER['DOCUMENT_ROOT'].'/ContentTemplates/Content/'.ucFirst($typeID);
                if ($i==0){
                    $fileName = $filenameBase.'.twig';
                } else {
                    $fileName = $filenameBase.'_'.$templateViewNames[$i].'.twig';
                }
                file_put_contents($fileName,$templateViewContents[$i]);
            }

			if (isset($_POST['afterAction']) && $_POST['afterAction']==''){
				$typeID='';
				$label='';
				$viewer = '';
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



$form = formConstruct('Add Content Type',$afterActions);
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


$webPage = webPageConstruct('Add New Content Type');
$webPage->find('h1')->before(constructMenu('contentTypesView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	