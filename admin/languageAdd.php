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

$allLangs = include($_SERVER['DOCUMENT_ROOT'].'/Config/Languages/languages.php');

$activeLangs = DB::col(Query::Select('languages')->fields('langID'));

foreach ($activeLangs as $activeLang){
    unset($allLangs[$activeLang]);
}


if (isset($_POST['lang'])){
    $lastRank = DB::val(Query::Select('languages')->fields('rank')->desc('rank')->limit(1));
    $newRank = $lastRank+10;
    DB::query(Query::Insert('languages')->fields('langID','rank')->values($_POST['lang'],$newRank));
    DB::query('ALTER TABLE `mlstrings` ADD '.DB::escape($_POST['lang']).' TEXT');
    redirect('languageView.php');
}

$form = formConstruct('Add Language');
$fieldset1='<fieldset class="col1 first">';
foreach ($allLangs as $code=>$lang) {
    $fieldset1 .= '<label><input type="radio" name="lang" value="'.$code.'">';

    $fieldset1.='&nbsp;<img src="Gfx/Flags/'.$lang['flag'].'.png"/>&nbsp;';
    $fieldset1.='<span>'.$lang['desc'].' ('.$lang['name'].')</span>';
    $fieldset1.='</label>';
    $fieldset1.='</label>';

}
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);


$webPage = webPageConstruct('Add Language');
$webPage->find('h1')->before(constructMenu('languageAdd.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);