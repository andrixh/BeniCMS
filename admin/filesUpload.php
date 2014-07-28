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
require_once('Routines/filesystem.php');


$form = formConstruct('Upload All Files');
$form->find('fieldset.submit')->before('<iframe class="upload" scrolling=no src="filesUploadFrame.php"></iframe>');
$form->find('fieldset.submit')->find('input[type=submit]')->parent()->before('<label><span></span><a href="#" class="formButton addIframe" data-iframeSrc="filesUploadFrame.php" data-iFrameClass="upload">Upload Another</a></label>');

require_script('Scripts/iframeForms.js');
$webPage = webPageConstruct('Upload Files');
$webPage->find('h1')->before(constructMenu('filesUpload.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	
