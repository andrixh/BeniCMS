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
require_once('Lib/phpass/PasswordHash.php');

rolePass(ROLE_ADMIN);

require_table('admins');

$afterActions = [
	[
		'label'=>'add another',
		'url'=>'',
	],
	[
		'label'=>'edit new Administrator',
		'url'=>'adminEdit.php?id=%id%',
		'default'=>true,
	],
    [
		'label'=>'go back',
		'url'=>'adminView.php'
	]
];


$perform=false;

// Init default form variables.
$userName='';
$password='';
$password2='';
$fullName='';
$email='';
$active=false;
$role = 0;

$id = -1;

$roleOptions = ['0'=>'User','1'=>'Administrator','2'=>'Developer'];

if (!roleCheck(ROLE_DEV)) {
	unset($roleOptions['2']);
}
if (!roleCheck(ROLE_ADMIN)) {
	unset($roleOptions['1']);
}

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	
	$userName=strtolower($_POST['userName']);
	$password=$_POST['password'];
	$password2=$_POST['password2'];
	$fullName=$_POST['fullName'];
	$email=strtolower($_POST['email']);
	$active=isset($_POST['active'])?true:false;
	$role = $_POST['role'];

	$valid=true; // no detected errors as a start - success assumed
	
	//Check if username is long enough
	if (strlen($userName) < conf('USER_NAME_LENGTH_MIN') || strlen($userName) > conf('USER_NAME_LENGTH_MAX') ) {
		$valid = false;
		addFormError('userName','Username must be between '.conf('USER_NAME_LENGTH_MIN').' and '.conf('USER_NAME_LENGTH_MAX').' characters long');
	}	
	//Check if username exists
	$exists = DB::val(Query::Select('admins')->fields('ID')->eq('userName',$userName));

	if ($exists) {
		$valid = false;
		addFormError('userName','This username is already registered');
	}
	//Check if password is long enough
	if (strlen($password) < conf('USER_PASS_LENGTH_MIN')) {
		$valid = false;
		addFormError('password','Password must be at least '.conf('USER_PASS_LENGTH_MIN').' characters long');
	}
	//Check if passwords match
	if ($password != $password2) {
		$valid = false;
		addFormError('password2','Passwords don\'t match');
	}
	//Check if Full Name is usable (has at least one space between letters)
	if (!validFullName($fullName)) {
		$valid = false;
		addFormError('fullName','Please enter a valid full name');
	}
	
	//Check if valid email
	if (!validEmailAddress($email)){
		$valid = false;
		addFormError('email','Please enter valid email');
	}

	if ($valid){ //if no errors, insert into database
        $passwordHash = new PasswordHash(conf("PASSWORD_HASH_COST"),conf("PORTABLE_HASHES"));
		$queryValues = [
			'userName'=>$userName,
			'password'=>$passwordHash->HashPassword($password),
			'fullName'=>$fullName,
			'email'=>$email,
			'active'=>$active,
			'role'=>$role
		];
		$query = Query::Insert('admins')->pairs($queryValues);
		$result = DB::query($query);
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			setError('Administrator "'.$userName.'" created',0);
			$insertID = DB::insert_id();
			if (isset($_POST['afterAction']) && $_POST['afterAction']==''){
				$userName = '';
				$password = '';
				$password2 = '';
				$fullName = '';
				$email ='';
				$active = false;
			}else if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $insertID, $_POST['afterAction']));	
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}




$form = formConstruct('Add Administrator',$afterActions);
$fieldset1='<fieldset class="col1 first"><h2>User Account</h2>';
$fieldset1.=field(label('User Name'),control_textInput('userName', $userName),'userName');
$fieldset1.=field(label('Email'),control_textInput('email', $email),'email');
$fieldset1.=field(label('Password'),control_password('password', $password),'password');
$fieldset1.=field(label('Repeat Password'),control_password('password2', $password2),'password2');

$fieldset1.='</fieldset><fieldset class="col1"><h2>Access Control</h2>';
$fieldset1.=field(label('Role'),control_select('role', $role,$roleOptions),'role');
$fieldset1.=field(label_checkbox('Active'),control_checkbox('active', $active),'active');
$fieldset1.='<h2>User Information</h2>';
$fieldset1.=field(label('Full Name'),control_textInput('fullName', $fullName),'fullName');

$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);


$webPage = webPageConstruct('Add Administrator');
$webPage->find('h1')->before(constructMenu('adminAdd.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	