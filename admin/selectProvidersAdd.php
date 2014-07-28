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
		'label'=>'add another Provider',
		'url'=>'',
	),
	array(
		'label'=>'edit new Provider',
		'url'=>'selectProvidersEdit.php?id=%id%',
	),array(
		'label'=>'go back',
		'url'=>'selectProvidersView.php',
		'default'=>true,
	)
);


$perform=false;
// Init default form variables.
$providerID='';
$label = '';
$options='{}';
$id = -1;

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$providerID=$_POST['providerID'];
	$label=$_POST['label'];
	$options=$_POST['options'];
	

	$valid=true; // no detected errors as a start - success assumed
	
	if (strlen($providerID) < 1 || strlen($providerID) > 255 || !allowedChars($providerID,conf('ALPHANUM').'_-.')) {
		$valid = false;
		addFormError(1,'Provider ID is required, and can contain only letters and numbers.');
	}	
	
	$exists = DB::val(Query::Select('selectproviders')->fields('id')->eq('providerID', $providerID));
	if ($exists) {
		$valid = false;
		addFormError(1,'This Provider ID already exists');
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
		$query = Query::Insert('selectproviders')->pairs($queryFields); _d($query);
		$result = DB::query($query);
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			$insertID = DB::insert_id();
			setError('Provider "'.$label.'" created',0);
			if (isset($_POST['afterAction']) && $_POST['afterAction']==''){
				$providerID='';
				$label = '';
				$options='[]';
				$id = -1;
			}else if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $insertID, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}



$form = formConstruct('Add Provider',$afterActions);
$fieldset1='<fieldset class="col1 first">';
$fieldset1.=field(label('Provider ID'),control_textInput('providerID', $providerID),1);
$fieldset1.=field(label('Provider Label'),control_textInput('label', $label),2);
$fieldset1.=field(label('Options'),control_customList('options', $options),3);
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);

$webPage = webPageConstruct('Add Select Control Provider');
$webPage->find('h1')->before(constructMenu('selectProvidersView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	
