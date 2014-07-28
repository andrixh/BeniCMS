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
//require_once('Routines/filesystem.php');
require_once('Routines/mlstrings.class.php');

$afterActions = array(
	array(
		'label'=>'stay here',
		'url'=>'',
		'default'=>true,
	),array(
		'label'=>'go back',
		'url'=>'interfaceView.php'
	)
);

require_table('interface');

$perform=false;

// Init default form variables.
if (isset($_GET['id'])){
	$id = $_GET['id'];
	$currentRecord = DB::row(Query::Select('interface')->fields('strID','value')->id($id));

	$strID=$currentRecord->strID;
	$strID_O=$strID;
	$value=mlString::Create($currentRecord->value)->postName('value');
} 

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$id = $_POST['id'];
	$strID=$_POST['strID'];
	$strID_O=$_POST['strID_O'];
	$value->fromPost('value');
	
	$valid = true;
	if (strlen($strID) < 1 || strlen($strID)>80){
		$valid = false;
		addFormError(1,'String ID must be between 1 and 80 letters long.');
	}

	if (!allowedChars($strID,conf('ALPHANUM').'_-.')) {
		$valid = false;
		addFormError(1,'String ID may contain only letters, numbers, and charachters "._-".');
	}	
	
	if ($strID!=$strID_O){
		$exists = DB::val(Query::Select('interface')->fields('id')->eq('strID', $strID)->limit(1));
		if ($exists) {
			$valid = false;
			addFormError(1,'This String ID already exists');
		}
	}

	if ($valid){
		$queryFields = array();
		$queryFields['strID']=$strID;
		$queryFields['value']=$value->strID;
		$result = DB::query(Query::Update('interface')->pairs($queryFields)->eq('id',$id));
		_d($result,'Result');
		if ($result === false){
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			$value->save();
			$strID_O=$strID;
			setError('Static String "'.$strID.'" updated',0);
			if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $id, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}
	


$form = formConstruct('Edit Static String', $afterActions);
$fieldset1='<fieldset class="col1">';
$fieldset1.=field(label('String ID','letters, numbers, and ".-_" charachters.'),control_textInput('strID', $strID),1);
$fieldset1.=field(label('Text Values'),control_mlTextArea('value', $value->getValues()));
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);
$form->find('fieldset.submit')->append(control_hidden('id', $id));
$form->find('fieldset.submit')->append(control_hidden('strID_O', $strID_O));


$webPage = webPageConstruct('Edit Static String');
$webPage->find('h1')->before(constructMenu('interfaceView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	