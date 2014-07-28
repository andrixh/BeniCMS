<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/db.tables.inc.php');
require_once('Includes/errors.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/form.inc.php');
require_once('Routines/validators.php');
require_once('Routines/mlstrings.class.php');

$afterActions = array(
	array(
		'label'=>'add another',
		'url'=>'',
	),
	array(
		'label'=>'add another',
		'clear'=>false,
		'url'=>'interfaceAdd.php',
	),
	array(
		'label'=>'edit new Interface String',
		'url'=>'interfaceEdit.php?id=%id%',
	),array(
		'label'=>'go back',
		'url'=>'InterfaceView.php',
		'default'=>true,
	)
);

require_table('interface');

$perform=false;
// Init default form variables.
$strID = '';
$value = mlString::Create()->postName('value')->usedTable('interface');

$id = -1;


if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$strID=$_POST['strID'];
	
	$value->fromPost();


	$valid=true; // no detected errors as a start - success assumed
	
	if (strlen($strID) < 1 || strlen($strID)>80){
		$valid = false;
		addFormError(1,'String ID must be between 1 and 80 letters long.');
	}

	if (!allowedChars($strID,conf('ALPHANUM').'_-.')) {
		$valid = false;
		addFormError(1,'String ID may contain only letters, numbers, and charachters "._-".');
	}	
	
	$exists = DB::val(Query::Select('interface')->fields('id')->eq('strID', $strID)->limit(1));
	if ($exists) {
		$valid = false;
		addFormError(1,'This String ID already exists');
	}
	
	//_d($values,'values');
	
	if ($valid){ //if no errors, insert into database
		$queryFields = array();
		//$queryFields = array_merge($queryFields,$values);
		$queryFields['strID']=$strID;
		$queryFields['value']=$value->strID;
		
		
		$result = DB::query(Query::Insert('interface')->pairs($queryFields));
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			$insertID = DB::insert_id();
			$value->usedID($insertID);
			$value->save();
			setError('Static String "'.$strID.'" created',0);
			
			_d('clearing form values');
			$strID='';
			$value = mlString::Create()->postName('value')->usedTable('interface');
			
			if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $insertID, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}




$form = formConstruct('Add Static String',$afterActions);
$fieldset1='<fieldset class="col1 first">';
$fieldset1.=field(label('String ID','letters, numbers, and ".-_" charachters.'),control_textInput('strID', $strID),1);
$fieldset1.=field(label('Text Values'),control_mlTextArea('value', $value->getValues()));
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);

$pageTitle = 'Add Interface String';
//if (isset($_GET['id'])){ $pageTitle = 'Duplicate Static String';}


$webPage = webPageConstruct($pageTitle);
$webPage->find('h1')->before(constructMenu('interfaceAdd.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	