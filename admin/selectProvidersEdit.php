<?php 
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/form.inc.php');
require_once('Routines/validators.php');
require_once('Routines/mlstrings.php');
require_once('Routines/selectProviders.php');


$afterActions = array(
	array(
		'label'=>'stay here',
		'url'=>'',
		'default'=>true,
	),array(
		'label'=>'go back',
		'url'=>'selectProvidersView.php'
	)
);

$perform=false;
// Init default form variables.
if (isset($_GET['id'])){
	$id = $_GET['id'];
	$currentRecord =DB::row(Query::Select('selectproviders')->fields('providerID','label','options','useCount','editorUrl')->eq('id', $id));
	$editorUrl = $currentRecord->editorUrl;
	if ($editorUrl) {
		redirect($editorUrl);
	}
	$providerID=$currentRecord->providerID;
	$providerID_O=$providerID;
	$label = $currentRecord->label;
	$options=$currentRecord->options;
	$useCount = $currentRecord->useCount;
} 

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$id = $_POST['id'];
	if ($useCount == 0){ 
		$providerID=$_POST['providerID'];
		$providerID_O = $_POST['providerID_O'];
	}
	$label=$_POST['label'];
	$options=$_POST['options'];
	
	$valid=true; // no detected errors as a start - success assumed
	
	if (strlen($providerID) < 1 || strlen($providerID) > 255 || !allowedChars($providerID,conf('ALPHANUM').'_-.')) {
		$valid = false;
		addFormError(1,'Provider ID is required, and can contain only letters and numbers.');
	}	
	
	if ($providerID != $providerID_O){
		$exists = DB::val(Query::Select('selectproviders')->fields('id')->eq('providerID', $providerID));
		if ($exists) {
			$valid = false;
			addFormError(1,'This Provider ID already exists');
		}
	}
	
	if (strlen($label) < 1 || strlen($label) > 255 || !allowedChars($label,conf('ALPHANUM').'-_. ')) {
		$valid = false;
		addFormError(2,'Label is required, and cannot contain special characters.');
	}
	
	if ($valid){ //if no errors, insert into database
		$queryFields = array(
			'providerID'=>$providerID,
			'label'=>$label,
			'options'=>$options
		);
		$query = Query::Update('selectproviders')->pairs($queryFields)->eq('id',$id);
		$result = DB::query($query);
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
				
			setError('Provider "'.$label.'" modified',0);
			if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $insertID, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}



$form = formConstruct('Edit Provider',$afterActions);
$fieldset1='<fieldset class="col1 first">';
if ($useCount == 0){
	$fieldset1.=field(label('Provider ID'),control_textInput('providerID', $providerID),1);
} else {
	$fieldset1.=control_hidden('providerID', $providerID);
}
$fieldset1.=field(label('Provider Label'),control_textInput('label', $label),2);
$fieldset1.=field(label('Options'),control_customList('options', $options),3);
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);
$form->find('fieldset.submit')->append(control_hidden('id', $id));
$form->find('fieldset.submit')->append(control_hidden('typeID_O', $providerID_O));

$webPage = webPageConstruct('Edit Select Control Provider');
$webPage->find('h1')->before(constructMenu('selectProvidersView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	
