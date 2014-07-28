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

if (conf
    ('HTTP_HOST_DEV') != $_SERVER['SERVER_NAME']){
    redirect('index.php');
}

$userName='';
$password='';
$password2='';
$fullName='';
$email='';
$active=true;
$role = 2;


if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted

    $userName=strtolower($_POST['userName']);
    $password=$_POST['password'];
    $password2=$_POST['password2'];
    $fullName=$_POST['fullName'];
    $email=strtolower($_POST['email']);

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
        $queryValues = array(
            'userName'=>$userName,
            'password'=>$passwordHash->HashPassword($password),
            'fullName'=>$fullName,
            'email'=>$email,
            'active'=>$active,
            'role'=>$role
        );
        $query = Query::Insert('admins')->pairs($queryValues);
        $result = DB::query($query);
        if ($result===false) {
            setError('Database Error! Please contact your webmaster!',2);
        } else {
            setError('User "'.$userName.'" created',0);
            redirect('login.php');
        }
    } else {
        setError('Your form contains errors, please review and post again!',1);
    }
}


$form = formConstruct('Create Administrator');
$fieldset1='<fieldset class="col1 first">';
$fieldset1.=field(label('User Name'),control_textInput('userName', $userName),"userName");
$fieldset1.=field(label('Password'),control_password('password', $password),"password");
$fieldset1.=field(label('Repeat Password'),control_password('password2', $password),"password2");
$fieldset1.=field(label('Full Name'),control_textInput('fullName', $fullName),"fullName");
$fieldset1.=field(label('Email'),control_textInput('email', $email),"email");
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);

$webPage = webPageConstruct('Create Administrator',$pageStyles, $pageScripts);
$webPage->find('body')->addClass('full');
$webPage->find('h1')->before('<img class="logoLarge" src="Gfx/LogoLarge.png"/>');
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());

echo outputWebPage($webPage);