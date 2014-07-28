<?php
session_name(md5($_SERVER['SERVER_NAME'].'admin'));
session_start();
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/db.tables.inc.php');
require_once('Includes/errors.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/form.inc.php');
require_once('Routines/validators.php');

require_table('users_resetpassword');

if (isset($_GET['rc']) && strlen($_GET['rc']) == 8){
	$canChange = true;	
	$rqCode = $_GET['rc'];
    $validQuery = Query::Select('users_resetpassword')->eq('resetCode', $rqCode);
    $requestRow = DB::row($validQuery);
    _d($requestRow,'$requestRow');
	if (!$requestRow){
		$canChange = false;
	} else if ($requestRow->expired == 1) {
		$canChange = false;
	} else {
		$userQuery = Query::Select('admins')->fields('userName','active','email','fullName')->eq('userName',$requestRow->userName);
		$userData = DB::row($userQuery);
		_d($userData,'$userData');
		if (!$userData){
			$canChange = false;
		} else if ($userData->active == 0){
			$canChange = false;
		}
	}
	if ($canChange){
		$newPassword = '';
		$newPassword2 = '';
		
		if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
			$newPassword=$_POST['newPassword'];
			$newPassword2=$_POST['newPassword2'];
			$userName = $userData->userName;
			
			$valid=true;
				
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
				$queryValues = array(
					'password'=>sha1($newPassword),
				);
				$query = Query::Update('users')->pairs($queryValues)->eq('userName',$userName);
				$result = DB::query($query);
				if ($result === false) {
					setError('Database Error! Please contact your webmaster!',2);
				} else {
					$expireQuery = Query::Update('users_resetpassword')->pairs(array('expired'=>1,'used'=>1))->eq('resetCode',$requestRow->resetCode);
					DB::query($expireQuery);
					
					require_style('Css/admin.css');
					$webPage = webPageConstruct('Pasword Reset Success',$pageStyles, $pageScripts);
					$webPage->find('h1')->before('<a href="/admin"><img class="logoLarge" src="Gfx/LogoLarge.png"/></a>');
					$webPage->find('body')->append('<p>Your password was reset sucessfully.</p>');
					$webPage->find('body')->append('<p>You can now <a href="/admin">login</a> using your new password</p>');
					echo outputWebPage($webPage);			
					die ();
				}
			} else {
				setError('Your form contains errors, please review and post again!',1);
			}
		}
			
		$form = formConstruct('Change Password');
		$fieldset1='<fieldset class="col1">';
		$fieldset1.=field(label('New Password'),control_password('newPassword', $newPassword),2);
		$fieldset1.=field(label('Repeat New Password'),control_password('newPassword2', $newPassword2),3);
		$fieldset1.='</fieldset>';
		$form->find('fieldset.submit')->before($fieldset1);
		
		$webPage = webPageConstruct('Reset your password',$pageStyles, $pageScripts);
		$webPage->find('h1')->before('<a href="/admin"><img class="logoLarge" src="Gfx/LogoLarge.png"/></a>');
		$webPage->find('h1')->after($form);
		$webPage->find('h1')->after(generateMessageBar());
		echo outputWebPage($webPage);	
		die();
	} else {
		require_style('Css/admin.css');
		$webPage = webPageConstruct('Pasword Reset Error',$pageStyles, $pageScripts);
		$webPage->find('h1')->before('<a href="/admin"><img class="logoLarge" src="Gfx/LogoLarge.png"/></a>');
		$webPage->find('body')->append('<p>The reset code is not correct or your username is no longer active.</p>');
		$webPage->find('body')->append('<p>Please try to <a href="/admin/loginResetPassword.php">reset your password</a> again.</p>');
		$webPage->find('body')->append('<p>If you are still unable to change your password, please contact your webmaster.</p>');
		echo outputWebPage($webPage);			
		die ();
	}

}




$email = '';

if (isset($_POST['perform']) && ($_POST['perform']==1)){
	$email = $_POST['email'];
	$valid = true;
	if (!validEmailAddress($email)) {
		$valid = false;
		setError('Please enter your email.',1);
	}
	
	if ($valid) {
        //display success message at all occasions, so malicious users are not aware if their operation went through.
        $existsQuery = Query::Select('admins')->eq('email', $email)->limit(1);
        $userData = DB::row($existsQuery);
        if ($userData!== false && $userData->active!=0) {

            //some maintenance first
            //invalidate requests older than 24 hours
            //delete requests older than 30 days
            $time = time();
            $expireQuery = Query::Update('users_resetpassword')->pairs(array('expired'=>1))->lt('time',$time - (24 * 60 * 60));
            DB::query($expireQuery);
            $deleteQuery = Query::Delete('users_resetpassword')->lt('time',$time - (30* 24 * 60 * 60));
            DB::query($deleteQuery);
            //end of maintenance


            $resetCode = generatePassword(8);
            $time = time();
            $userIP = $_SERVER['REMOTE_ADDR'];
            $resetUserName = $userData->userName;
            $userEmail = $userData->email;

            $queryFields = array(
                'resetCode'=>$resetCode,
                'userName'=>$resetUserName,
                'time'=>$time,
                'ip'=>$userIP,
                'expired'=>0,
                'used'=>0
            );
            $resetQuery = Query::Insert('users_resetpassword')->pairs($queryFields);
            DB::query($resetQuery);

            $updateQuery = Query::Update('users_resetpassword')->pairs(array('expired'=>1))->ieq('resetCode',$resetCode);
            DB::query($updateQuery);

            $to = $userEmail;
            $from = 'admin_accounts@'.conf('HTTP_HOST_LIVE');
            $subject = 'Reset your password';
            $body = 'Hello, '.$userData->fullName.'!'. "\r\n".
                'There was a request to change your password for '.conf('HTTP_HOST_LIVE').'.'. "\r\n".
                'If you did not make this request, just ignore this email.'. "\r\n".
                'Otherwise, please click the link below to change your password:'. "\r\n".
                "\r\n".
                'http://'.$_SERVER['HTTP_HOST'].'/admin/loginResetPassword.php?rc='.$resetCode. "\r\n".
                "\r\n".
                'If you have not requested the change yourself and keep receiving these messages, please contact your webmaster. Thank you.';

            _gc('mailing');
            _d($to,'$to');_d($from,'$from');_d($subject,'$subject');_d($body,'$body');
            _u();
            $headers = 'From: '.$email;
            if ($_SERVER['HTTP_HOST'] == conf('HTTP_HOST_LIVE')) {
                mail($to, $subject, $body, $headers);
            }
        }
        require_style('Css/admin.css');
		$webPage = webPageConstruct('Reset Password',$pageStyles, $pageScripts);
		$webPage->find('h1')->before('<a href="/admin"><img class="logoLarge" src="Gfx/LogoLarge.png"/></a>');
		$webPage->find('body')->append('<p>Thank you.</p>');
		$webPage->find('body')->append('<p>An email with instructions on how to reset your password has been sent to your email address.</p>');
		$webPage->find('body')->append('<p>You should be receiving it shortly.</p>');
		echo outputWebPage($webPage);			
		die ();
		
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
		
	
}

$form = formConstruct('Reset my Password');
$fieldset1='<fieldset class="col1 first">';
$fieldset1.=field(label('Email'),control_textInput('email', $email),1);
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);


$webPage = webPageConstruct('Reset Password',$pageStyles, $pageScripts);
$webPage->find('body')->addClass('full');
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/branding/LogoLarge.png')) {
    $webPage->find('h1')->before('<img class="logoLarge" src="/branding/LogoLarge.png"/>');
} else {
    $webPage->find('h1')->before('<img class="logoLarge" src="Gfx/LogoLarge.png"/>');
}
$webPage->find('h1')->after($form);

$webPage->find('h1')->after(generateMessageBar());

echo outputWebPage($webPage);	
