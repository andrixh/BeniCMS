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
require_once('Routines/mlstrings.class.php');

$afterActions = array(
	array(
		'label'=>'stay here',
		'url'=>'',
		'default'=>true,
	),array(
		'label'=>'go back',
		'url'=>'mlstringsView.php'
	)
);

$perform=false;

// Init default form variables.

if (isset($_GET['id'])){
	$id = $_GET['id'];
	$query = Query::Select('mlstrings')->fields('strID')->eq('id',$id)->limit(1);
	$currentRecord = DB::row($query);
	$mlString = mlString::Create($currentRecord->strID)->postName('mlString');
}

if (isset($_GET['strID'])){
	$id = DB::val(Query::Select('mlstrings')->fields('ID')->eq('strID', $_GET['strID']));
	$mlString = mlString::Create($_GET['strID'])->postName('mlString');
} 

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$id = $_POST['id'];
	$mlString->fromPost();
	$valid = true;

	if ($valid){
		$mlString->save(true);
		setError('Dynamic String "'.$strID_O.'" updated',0);
		if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
			redirect(str_replace('%id%', $id, $_POST['afterAction']));
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}

$form = formConstruct('Edit Dynamic String', $afterActions);
$fieldset1='<fieldset class="col2">';
$fieldset1.='<h2>Dynamic String "'.$mlString->strID.'" <br/> Used in table "'.$mlString->usedTable().'" on ID '.$mlString->usedID().'</h2>';
$fieldset1.=field(label('Raw Values'),control_mlTextArea('mlString', $mlString->getValues()));
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);
$form->find('fieldset.submit')->append(control_hidden('id', $id));


$webPage = webPageConstruct('Edit Dynamic String');
$webPage->find('h1')->before(constructMenu('mlstringsView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);