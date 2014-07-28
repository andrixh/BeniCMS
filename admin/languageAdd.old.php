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

$afterActions = array(
	array(
		'label'=>'add another Language',
		'url'=>'',
	),
	array(
		'label'=>'edit new Language',
		'url'=>'languageEdit.php?id=%id%',
	),array(
		'label'=>'go back',
		'url'=>'languageView.php',
		'default'=>true,
	)
);


$perform=false;
// Init default form variables.
$langID='';
$name='';
$localName='';
$flag='';
$active=false;
$main=false;
$rank=20;

$id = -1;

if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$langID=mb_strtoupper($_POST['langID'],"UTF-8");
	$name=mb_strtolower($_POST['name'],"UTF-8");
	$localName=mb_strtolower($_POST['localName'],"UTF-8");
	$active=isset($_POST['active'])?true:false;
	$main=isset($_POST['main'])?true:false;
	$flag=$_POST['flag'];
	$rank=$_POST['rank'];

	$valid=true; // no detected errors as a start - success assumed
	
	
	if (strlen($langID) != 2 || !allowedChars($langID,conf('ALPHA_UPPER'))) {
		$valid = false;
		addFormError(1,'Must be 2 charachters long, and contain only letters.');
	}	
	
	$exists = DB::val(Query::Select('languages')->fields('id')->eq('langID', $langID)->limit(1));
	if ($exists) {
		$valid = false;
		addFormError(1,'This language already exists');
	}
	
	if (strlen($name) < 3 || strlen($name) > 20 || !allowedChars($name,conf('ALPHA_LOWER'))){
		$valid = false;
		addFormError(2,'Must be 3-20 charachters long, and contain only letters.');
	}

	if (strlen($localName) < 3 || strlen($localName) > 20 || !allowedChars($localName,conf('ALPHA_LOWER').conf('DIACRITICS'))){
		$valid = false;
		addFormError(3,'Must be 3-20 charachters long, and contain only letters.');
	}
	 
	if ($flag == '') {
		$valid = false;
		addFormError(4, 'Please select a flag');
	}
	
	if (!valid_int($rank) || $rank <0) {
		$valid = false;
		addFormError(5, 'Rank must be a positive integer number.');
	}
	
	if ($valid){ //if no errors, insert into database
        setError('Your form contains errors, please review and post again!',1);
        $queryFields = array(
            'langID'=>$langID,
            'name'=>$name,
            'localName'=>$localName,
            'flag'=>$flag,
            'active'=>$active,
            'main'=>$main,
            'rank'=>$rank
        );
        $result = DB::query(Query::Insert('languages')->pairs($queryFields));
        if ($result===false) {
            setError('Database Error! Please contact your webmaster!',2);
        } else {
            $insertID = DB::insert_id();
            DB::query('ALTER TABLE `mlstrings` ADD '.DB::escape($langID).' TEXT');
            if ($main == true){
                DB::query(Query::Update('languages')->pairs(array('main'=>0))->ieq('ID',$insertID));
            }
            setError('Language "'.ucfirst($name).'" created',0);
            if (isset($_POST['afterAction']) && $_POST['afterAction']==''){
                $langID='';
                $name='';
                $localName='';
                $flag='';
                $active=false;
                $main=false;
                $rank=20;
            }else if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
                redirect(str_replace('%id%', $insertID, $_POST['afterAction']));
            }
        }
    } else {
        setError('Your form contains errors, please review and post again!',1);
    }
}



$form = formConstruct('Add Language',$afterActions);
$fieldset1='<fieldset class="col1 first">';
$fieldset1.='<h2>Language Properties</h2>';
$fieldset1.=field(label('Language ID','2-letter code'),control_textInput('langID', $langID),1);
$fieldset1.=field(label('Language Name','(in english)'),control_textInput('name', $name),2);
$fieldset1.=field(label('Local Language Name','(in new language)'),control_textInput('localName', $localName),3);
$fieldset1.=field(label('Flag'),control_selectImage('flag', $flag, provide_files('Gfx/Flags', 'png')),4);
$fieldset1.='</fieldset><fieldset class="col1">';
$fieldset1.='<h2>Visibility and Positioning</h2>';
$fieldset1.=field(label('Rank','(lowest appears first)'),control_textInput('rank', $rank),5);
$fieldset1.=field(label_checkbox('Active'),control_checkbox('active', $active));
$fieldset1.=field(label_checkbox('Main'),control_checkbox('main', $main));
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);

$webPage = webPageConstruct('Add Language');
$webPage->find('h1')->before(constructMenu('languageAdd.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	
