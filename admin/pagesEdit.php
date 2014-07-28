<?php 
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/db.tables.inc.php');
require_once('Includes/errors.inc.php');
require_once('Includes/counter.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/form.inc.php');
require_once('Routines/validators.php');
require_once('Routines/filesystem.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/content.php');
require_once('Routines/selectProviders.php');


require_table('pages');

$afterActions = array(
	array(
		'label'=>'stay here',
		'url'=>'',
		'default'=>true,
	),array(
		'label'=>'go back',
		'url'=>'pagesView.php',
	)
);


$perform=false;
// Init default form variables.
if (isset($_GET['id'])){
	$id = $_GET['id'];
    $currentRecord =DB::row(Query::Select('pages')->fields('pageID','type','main','parent','rank','rep','menuGroups','active','link','track','title','menuTitle','cache')->eq('id', $id));
    $pageID=$currentRecord->pageID;
    $pageID_O=$pageID;
    $type=$currentRecord->type;
    $typeData = DB::row(Query::Select('pagetypes')->fields('scheme','formTemplate')->eq('typeID', $type));
    $typeScheme = json_decode($typeData->scheme);
    $typeFormTemplate = $typeData->formTemplate;

    _d($typeData);
    $main=$currentRecord->main;
    $parent=$currentRecord->parent;
    $rank=$currentRecord->rank;
    $rep=$currentRecord->rep;
    $menuGroups = $currentRecord->menuGroups;

    $menuGroup1 = $menuGroups & 1;
    $menuGroup2 = $menuGroups & 2;
    $menuGroup3 = $menuGroups & 4;

    $active=$currentRecord->active;
    $link=$currentRecord->link;
    if ($link!=''){
        $inPages = DB::val(Query::Select('pages')->fields('ID')->eq('pageID', $link));
		if ($inPages) {
			$link_in = $link;
			$link_out = '';
		} else {
			$link_in = '';
			$link_out = $link;
		}
	} else {
		$link_in = '';
		$link_out = '';
	}
	$track=$currentRecord->track;
    $cache=$currentRecord->cache;
	$title=mlString::Create($currentRecord->title)->postName('title');
	$menuTitle=mlString::Create($currentRecord->menuTitle)->postName('menuTitle');

    $contentTable = 'pages_'.$type;

    $contentRecord = DB::row(Query::Select($contentTable)->eq('pageID',$pageID_O),DB::ASSOC);

	$content=pageContentEstivate($contentRecord, $typeScheme);



	_d($content,'estivated content');
	countContentResources($content,$typeScheme,-1);
	_d($_counter);
}

//perform goes here
if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$id = $_POST['id'];
	$pageID=$_POST['pageID'];
	$pageID_O=$_POST['pageID_O'];
	$type=$_POST['type'];
	$typeData = DB::row(Query::Select('pagetypes')->fields('scheme','formTemplate')->eq('typeID', $type));
	$typeScheme = json_decode($typeData->scheme);
	$typeFormTemplate = $typeData->formTemplate;
	$main=isset($_POST['main'])?true:false;
	$parent=$_POST['parent'];
	$rank=$_POST['rank'];
	$rep=$_POST['rep'];

	$menuGroup1=isset($_POST['menuGroup1'])?1:0;
	$menuGroup2=isset($_POST['menuGroup2'])?2:0;
	$menuGroup3=isset($_POST['menuGroup3'])?4:0;
    $menuGroups = $menuGroup1 | $menuGroup2 | $menuGroup3;

	$active=isset($_POST['active'])?true:false;
	$track=isset($_POST['track'])?true:false;
	$link_in = $_POST['link_in'];
	$link_out = $_POST['link_out'];
    $cache=isset($_POST['cache'])?true:false;
	$title->fromPost();
	$menuTitle->fromPost();

	$content = array_merge($content,contentFromPost($content,$typeScheme));
    _d($content,'content From Post');
			
	$valid = contentValidate($content,$typeScheme);
	
	if (strlen($pageID) < 1 || strlen($pageID) > 255 || !allowedChars($pageID,conf('ALPHANUM').'-_') || !allowedChars(substr($pageID,0,1),conf('ALPHA'))){
		$valid = false;
		addFormError(1,'Page ID must be 1-255 charachters long, and cannot contain special characters.');
	}
	if ($pageID != $pageID_O){
        $content['pageID'] = $pageID;
		$exists = DB::val(Query::Select('pages')->fields('id')->eq('pageID', $pageID));
		if ($exists) {
			$valid = false;
			addFormError(1,'This Page ID already exists');
		}
	}
	
	if (isParentOf($parent,$pageID_O)){
		$valid = false;
		addFormError(2, 'Invalid Parent. Would create dependency loop.');
	}
	
	if (!valid_int($rank) || $rank <0) {
		$valid = false;
		addFormError(3, 'Rank must be a positive integer number.');
	}

	if (strlen($title->defaultValue()) == 0){
		$valid = false;
		addFormError(5, 'Page title is required in main language.');
	}
	
	if (strlen($menuTitle->defaultValue()) == 0){
		$valid = false;
		addFormError(6, 'Menu title is required in main language.');
	}
	
	if ($link_in != '' && $link_out!=''){
		$valid = false;
		addFormError(7, 'You cannot have both an internal and an external link.');
		addFormError(8, 'You cannot have both an internal and an external link.');
	}
	
	if ($valid){
		countContentResources($content,$typeScheme,1);
		
		$link = '';
		if ($link_in!=''){
			$link = $link_in;
		} else if ($link_out!=''){
			$link = $link_out;
		}
		
		$queryFields = array(
			'pageID'=>$pageID,
			'type'=>$type,
			'main'=>$main,
			'parent'=>$parent,
			'rank'=>$rank,
			'rep'=>$rep,
			'menuGroups'=>$menuGroups,
			'active'=>$active,
			'link'=>$link,
			'track'=>$track,
			'title'=>$title->strID,
			'menutitle'=>$menuTitle->strID,
            'cache'=>$cache
			//'content'=>json_encode(pageContentHibernate($content, $typeScheme)),
		);


		$query = Query::Update('pages')->pairs($queryFields)->eq('id',$id);
		$result = DB::query($query);
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
				
			$title->usedTable('pages')->usedID($id)->save();
			$menuTitle->usedTable('pages')->usedID($id)->save();

			$contentRecord = pageContentHibernate($content, $typeScheme);

            DB::query(Query::Update($contentTable)->pairs($contentRecord)->id($contentRecord['ID']));

            $mlstrings = pageContentExtractMlStrings($content,$typeScheme);



			foreach ($mlstrings as $mlstring) {
				$mlstring->usedTable($contentTable)->usedID($contentRecord['ID'])->save();
			}
			if ($main == true){
				DB::query(Query::Update('pages')->pairs(array('main'=>0))->ieq('ID',$id));
			}
			if ($pageID != $pageID_O) {
				DB::query(Query::Update('pages')->pairs(array('parent'=>$pageID))->eq('parent',$pageID_O));
			}
			commitCounts();
			setError('Page "'.$pageID.'" modified',0);
			if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $id, $_POST['afterAction']));
			}
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}


$form = formConstruct('Modify Page',$afterActions);
$fieldset1='<fieldset class="col2 first thin">';
$fieldset1.='</fieldset>';
$fieldset1.='<fieldset class="col1 first">';
$fieldset1.='<h2>Structure</h2>';
$fieldset1.=field(label('Page ID','unique'),control_textInput('pageID', $pageID),1);
$fieldset1.=control_hidden('type', $type);
$fieldset1.=field(label('Parent'),control_pageSelect('parent', $parent),2); 
$fieldset1.=field(label('Rank','(lowest appears first)'),control_textInput('rank', $rank),3);
$fieldset1.=field(label_checkbox('Home Page'),control_checkbox('main', $main));
$fieldset1.='<h2>Appearance</h2>';
$fieldset1.=field(label('Page Title'),control_mlTextInput('title', $title->getValues()),5);
$fieldset1.=field(label('Menu Title'),control_mlTextInput('menuTitle', $menuTitle->getValues()),6);
$fieldset1.='</fieldset>';
$fieldset1.='<fieldset class="col1">';

$fieldset1.='<h2>Visibility</h2>';
$fieldset1.=field(label_checkbox('Active'),control_checkbox('active', $active));
$fieldset1.=field(label_checkbox('Visible in Menu Group 1'),control_checkbox('menuGroup1', $menuGroup1));
$fieldset1.=field(label_checkbox('Visible in Menu Group 2'),control_checkbox('menuGroup2', $menuGroup2));
$fieldset1.=field(label_checkbox('Visible in Menu Group 2'),control_checkbox('menuGroup3', $menuGroup3));
$fieldset1.=field(label('Representative','page appearing selected when this page being viewed'),control_pageSelect('rep', $rep));
$fieldset1.='<h2>Link To</h2>';
$fieldset1.=field(label('A page in this site'),control_pageSelect('link_in', $link_in),7);
$fieldset1.=field(label('or An page in the Internet','start with "http://"'),control_textInput('link_out', $link_out),8);
$fieldset1.='<h2>Options</h2>';
$fieldset1.=field(label_checkbox('Track Visits','in Google Analytics'),control_checkbox('track', $track));
$fieldset1.=field(label_checkbox('Cache this page'),control_checkbox('cache', $cache));
$fieldset1.='</fieldset>';
$fieldset1.='<fieldset class="col2 first thin">';
$fieldset1.='<h2 class="">Content</h2>';
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);
$form->find('fieldset.submit')->append(control_hidden('id', $id));
$form->find('fieldset.submit')->append(control_hidden('pageID_O', $pageID));

$fieldset2 = contentFields($content, $typeScheme, $typeFormTemplate);

$form->find('fieldset.submit')->before($fieldset2);

$webPage = webPageConstruct('Modify Page');
$webPage->find('h1')->before(constructMenu('pagesView.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);	


