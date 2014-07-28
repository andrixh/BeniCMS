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

require_table('admins');

$afterActions = [
	[
		'label'=>'stay here',
		'url'=>'',
		'default'=>true,
	],
    [
		'label'=>'go back',
		'url'=>'adminView.php'
	]
];

$perform=false;

// Init default form variables.
if (isset($_GET['id'])){
	$id = $_GET['id'];
	$query = Query::Select('admins')->fields('userName','fullName','email','active','role')->eq('id',$id)->limit(1);
	$currentRecord = DB::row($query);
	$userName=$currentRecord->userName;
	$fullName=$currentRecord->fullName;
	$email=$currentRecord->email;
	$active=$currentRecord->active;
	$role=$currentRecord->role;
	rolePass($role,'You are not authorized to perform this action.');
} 

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$id = $_POST['id'];
	$fullName=$_POST['fullName'];
	$email=$_POST['email'];
	$active=isset($_POST['active'])?true:false;
	
	$valid = true;
	//Check if Full Name is usable (has at least one space between letters)
	if (!validFullName($fullName)) {
		$valid = false;
		addFormError(1,'Please enter a valid full name');	
	}
	//Check if valid email
	if (!validEmailAddress($email)){
		$valid = false;
		addFormError(2,'Please enter valid email');	
	}

	if ($valid){
		$queryValues = [
			'fullName'=>$fullName,
			'email'=>$email,
			'active'=>$active,
		];
		$query = Query::Update('admins')->pairs($queryValues)->eq('id',$id);
		$result = DB::query($query);
		_d($result,'Result');
		if ($result === false){
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			setError('Administrator "'.$userName.'" updated',0);
			if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $id, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}
	


$form = formConstruct('Edit Administrator', $afterActions);
$fieldset1='<fieldset class="col1">';
$fieldset1.='<h2>User Information</h2>';
$fieldset1.=field(label('Full Name'),control_textInput('fullName', $fullName),1);
$fieldset1.=field(label('Email'),control_textInput('email', $email),2);
$fieldset1.='<h2>User Account</h2>';
$fieldset1.=field(label_checkbox('Active'),control_checkbox('active', $active));
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);
$form->find('fieldset.submit')->append(control_hidden('id', $id));


$webPage = webPageConstruct('Edit Administrator');
$webPage->find('h1')->before(constructMenu('admninView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	