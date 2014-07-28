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
require_once('Lib/phpass/PasswordHash.php');

$afterActions = [
	[
		'label'=>'stay here',
		'url'=>'',
		'default'=>true,
	],
    [
		'label'=>'go back',
		'url'=>'/admin/adminView.php'
	]
];

$perform=false;

// Init default form variables.

$oldPassword='';
$newPassword='';
$newPassword2='';

$error = -1; //no errors, no success
$errorMsg = '';

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
    $passwordHash = new PasswordHash(conf("PASSWORD_HASH_COST"),conf("PORTABLE_HASHES"));
	$oldPassword=$_POST['oldPassword'];
	$newPassword=$_POST['newPassword'];
	$newPassword2=$_POST['newPassword2'];

	$valid=true;
		
	//Check if old password is entered at all
	if ($oldPassword=='') {
		$valid=false;
		addFormError(1,'Please enter current password');
	}
	
	//Check if current password is correct
	$oldPass = DB::val(Query::Select('admins')->fields('password')->eq('id',$_SESSION['loginID']));
	if (!$passwordHash->CheckPassword($oldPassword,$oldPass)) {
		$valid=false;		
		addFormError(1,'Current password is incorrect');
	}
	
	//Check if new password is long enough
	if (strlen($newPassword) < conf('USER_PASS_LENGTH_MIN')) {
		$valid=false;		
		addFormError(2,'Password must be at least '.conf('USER_PASS_LENGTH_MIN').' characters long');
	}
	
	//Check if passwords match
	if ($newPassword != $newPassword2) {
		$valid=false;	
		addFormError(3,'Passwords don\'t match');	
	}
	
	if ($valid){ //if no errors, insert into database
		$queryValues = [
			'password'=>$passwordHash->HashPassword($newPassword),
		];
		$query = Query::Update('admins')->pairs($queryValues)->eq('id',$_SESSION['loginID']);
		$result = DB::query($query);
		if ($result === false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			setError('Password Changed',0);
			if (isset($_POST['afterAction']) && $_POST['afterAction']==''){
				$oldPassword='';
				$newPassword='';
				$newPassword2='';
			}else if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect($_POST['afterAction']);
				_die();
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}
	
$form = formConstruct('Change Password',$afterActions);
$fieldset1='<fieldset class="col1">';
$fieldset1.=field(label('Old Password'),control_password('oldPassword', $oldPassword),1);
$fieldset1.=field(label('Password'),control_password('newPassword', $newPassword),2);
$fieldset1.=field(label('Repeat Password'),control_password('newPassword2', $newPassword2),3);
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);

$webPage = webPageConstruct('Change Password',$pageStyles, $pageScripts);
$webPage->find('h1')->before(constructMenu('adminChangePass.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);	