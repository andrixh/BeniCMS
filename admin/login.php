<?php
session_name(md5($_SERVER['SERVER_NAME'].'admin'));
session_start();
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/db.tables.inc.php');
require_once('Lib/phpass/PasswordHash.php');
require_once('Includes/errors.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/form.inc.php');
require_once('Routines/validators.php');


$userName = '';
$password = '';
require_table('admins');

//check if there are no admins and in local development

$q = Query::Select('admins')->fields('ID');
$admins = DB::col($q);
if (count($admins) == 0 && conf('HTTP_HOST_DEV') == $_SERVER['SERVER_NAME']) {
    redirect('init.php');
}


if (isset($_GET['doLogout'])&& $_GET['doLogout']==1) {
    session_destroy();
	setError('Logout Successful.',0);
	redirect('login.php');
}

if (isset($_POST['perform']) && ($_POST['perform']==1)){

	$userName = $_POST['userName'];
	$password = $_POST['password'];
	
	$valid = true;
	if ($_POST['userName']==''){
		$valid = false;
		addFormError(1,'Please enter username');	
	}

	if ($_POST['password']==''){
		$valid = false;
		addFormError(2,'Please enter password');	
	}
	
	
	if ($valid){
        $query = Query::Select('admins')->fields('ID','userName','password','active','role','fullName')->eq('userName',$userName)->w_or()->eq('email',$userName)->limit(1);
		$result = DB::row($query);
        $passwordHash = new PasswordHash(conf("PASSWORD_HASH_COST"),conf("PORTABLE_HASHES"));
		if (!$result) {
			setError('Invalid Username or Password. <a href="loginResetPassword.php">Forgot your Password?</a>',2);
            sleep(rand(1,5));
		} else if (!$passwordHash->CheckPassword($password,$result->password)){
			setError('Invalid Username or Password. <a href="loginResetPassword.php">Forgot your Password?</a>',2);
            sleep(rand(1,5));
		} else if ($result->active == FALSE){
			setError('Invalid Username or Password. <a href="loginResetPassword.php">Forgot your Password?</a>',2);
            sleep(rand(1,5));
		} else {
			$_SESSION['login']=true;
			$_SESSION['loginID']=$result->ID;
			$_SESSION['loginFullName']=$result->fullName;
			$_SESSION['loginUserName']=$result->userName;
			$_SESSION['loginRole']=$result->role;
			redirect('index.php');
            sleep(rand(1,5));
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}

$form = formConstruct('Login');
$fieldset1='<fieldset class="col1 first">';
$fieldset1.=field(label('User Name'),control_textInput('userName', $userName),1);
$fieldset1.=field(label('Password'),control_password('password', $password),2);
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);


$webPage = webPageConstruct('Administrative Login',$pageStyles, $pageScripts);
$webPage->find('body')->addClass('full');
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/branding/LogoLarge.png')) {
    $webPage->find('h1')->before('<img class="logoLarge" src="/branding/LogoLarge.png"/>');
} else {
    $webPage->find('h1')->before('<img class="logoLarge" src="Gfx/LogoLarge.png"/>');
}
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());

echo outputWebPage($webPage);	

