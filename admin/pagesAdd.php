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

$pageTypes = DB::get(Query::Select('pagetypes')->fields('typeID','label','icon','comment','rank','hidden')->asc('hidden')->asc('rank')->asc('label'));
$pTypes = phpQuery::newDocument('<ul class="pageTypes"></ul>');
if ($pageTypes){
    foreach ($pageTypes as $pageType){
        $pLink = phpQuery::newDocument('<li><a></a></li>');
        $pLink->find('a')->attr('href','_pageUtil.php?action=create&type='.$pageType->typeID.'&return=true');
        $pLink->find('a')->append('<img src="Gfx/PageTypes/'.$pageType->icon.'.png"/>');
        $pLink->find('a')->append('<span>'.$pageType->label.'</span>');
        if ($pageType->comment != '') {
            $pLink->find('a')->append('<em>'.$pageType->comment.'</em>');
        }
        if ($pageType->hidden == 1){
            $pLink->find('a')->addClass('shy');
        }
        $pTypes->find('ul')->append($pLink);
    }
} else {
    setError('No page types defined. To proceed, you must first define at least one page type.',0,false);
    $pTypes->find('ul')->append('<li><a href="pageTypesAdd.php"><img src="Gfx/PageTypes/Orb plus.png"><span>Add New Page Type</span></a></li>');
}

require_style('Css/pages.css');
require_style('Css/admin.css');
$webPage = webPageConstruct('Create new page');
$webPage->find('h1')->before(constructMenu('pagesAdd.php'));
$webPage->find('h1')->after($pTypes);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);

die();

/*

$afterActions = array(
	array(
		'label'=>'add another page',
		'url'=>'pagesAdd.php',
	),
	array(
		'label'=>'edit new Page',
		'url'=>'pagesEdit.php?id=%id%',
	),array(
		'label'=>'go back',
		'url'=>'pagesView.php',
		'default'=>true,
	)
);


$perform=false;
// Init default form variables.
$pageID='';
$type=$_GET['type'];
$typeData = DB::row(Query::Select('pagetypes')->fields('scheme','formTemplate')->eq('typeID', $type)->limit(1));
$typeScheme = json_decode($typeData->scheme);
$typeFormTemplate = $typeData->formTemplate;
_d($typeData);
$main=false;
$parent='';
$rank=20;
$rep='';
$inMainMenu=true;
$inSideMenu=true;
$inFooter=true;
$active=true;
$link_in ='';
$link_out ='';
$track=true;
$cache = false;
$title=mlString::Create()->usedTable('pages')->postName('title');
$menuTitle=mlString::Create()->usedTable('pages')->postName('menuTitle');
$content=contentCreate($typeScheme);
_g('CONTENT');
	_d($content);
_u();
$id = -1;

//perform goes here
if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted
	$pageID=$_POST['pageID'];
	$type=$_POST['type'];
	$typeData = DB::row(Query::Select('pagetypes')->fields('scheme','formTemplate')->eq('typeID', $type));
	$typeScheme = json_decode($typeData->scheme);
	$typeFormTemplate = $typeData->formTemplate;
	$main=isset($_POST['main']);
	$parent=$_POST['parent'];
	$rank=$_POST['rank'];
	$rep=$_POST['rep'];
	$inMainMenu=isset($_POST['inMainMenu']);
	$inSideMenu=isset($_POST['inSideMenu']);
	$inFooter=isset($_POST['inFooter']);
	$link_in = $_POST['link_in'];
	$link_out = $_POST['link_out'];
	$active=isset($_POST['active']);
	$track=isset($_POST['track']);
    $cache=isset($_POST['cache']);
	$title->fromPost();
	$menuTitle->fromPost();

	$content = contentFromPost($content,$typeScheme);
	
			
	$valid = contentValidate($content,$typeScheme);
	
	if (strlen($pageID) < 1 || strlen($pageID) > 255 || !allowedChars($pageID,conf('ALPHANUM').'-_')){
		$valid = false;
		addFormError(1,'Page ID must be 1-255 charachters long, and cannot contain special characters.');
	}
	
	$exists = DB::row(Query::Select('pages')->fields('id')->eq('pageID', $pageID));
	if ($exists) {
		$valid = false;
		addFormError(1,'This Page ID already exists');
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
		setCount('pageType', $type, 1);
		
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
			'inMainMenu'=>$inMainMenu,
			'inSideMenu'=>$inSideMenu,
			'inFooter'=>$inFooter,
			'active'=>$active,
			'link'=>$link,
			'track'=>$track,
			'title'=>$title->strID,
			'menuTitle'=>$menuTitle->strID,
            'cache'=>$cache,
			//'content'=>json_encode(pageContentHibernate($content, $typeScheme)),
		);
		
		$query = Query::Insert('pages')->pairs($queryFields);



		$result = DB::query($query);
		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			$insertID = DB::insert_id();

            if ($main == true){
                DB::query(Query::Update('pages')->pairs(array('main'=>0))->ieq('ID',$insertID));
            }


			$title->usedTable('pages')->usedID($insertID)->save();
			$menuTitle->usedTable('pages')->usedID($insertID)->save();

            $contentTable = 'pages_'.$type;


            $contentFields = pageContentHibernate($content, $typeScheme);
            $contentFields['pageID'] = $pageID;


            DB::query(Query::Insert($contentTable)->pairs($contentFields));
            $contentID = DB::insert_id();



            $mlstrings = pageContentExtractMlStrings($content, $typeScheme);
            _d($mlstrings,'extracted mlstrings from content');
			foreach ($mlstrings as $mlstring) {
				$mlstring->usedTable($contentTable)->usedID($contentID)->save();
			}

			commitCounts();
			setError('Page "'.$pageID.'" created',0);
			if (isset($_POST['afterAction']) && $_POST['afterAction']==''){
				$langID='';
				$name='';
				$localName='';
				$flag='';
				$active=false;
				$main=false;
				$rank=20;
			} else if (isset($_POST['afterAction']) && $_POST['afterAction']!=''){
				redirect(str_replace('%id%', $insertID, $_POST['afterAction']));
			}	
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}


$form = formConstruct('Add Page',$afterActions);
_gc('Hardcoded Controls');
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
$fieldset1.=field(label_checkbox('Visible in Main Menu'),control_checkbox('inMainMenu', $inMainMenu));
$fieldset1.=field(label('Representative','page appearing selected when this page being viewed'),control_pageSelect('rep', $rep)); 
$fieldset1.=field(label_checkbox('Visible in Side Menu'),control_checkbox('inSideMenu', $inSideMenu));
$fieldset1.=field(label_checkbox('Visible in Footer'),control_checkbox('inFooter', $inFooter));
$fieldset1.=field(label_checkbox('Active'),control_checkbox('active', $active));
$fieldset1.='<h2>Link To</h2>';
$fieldset1.=field(label('A page in this site'),control_pageSelect('link_in', $link_in),7);
$fieldset1.=field(label('or a page in the Internet','start with "http://"'),control_textInput('link_out', $link_out),8);
$fieldset1.='<h2>Options</h2>';
$fieldset1.=field(label_checkbox('Track Visits','in Google Analytics'),control_checkbox('track', $track));
$fieldset1.=field(label_checkbox('Cache this page'),control_checkbox('cache', $cache));
$fieldset1.='</fieldset>';
$fieldset1.='<fieldset class="col2 first thin">';
$fieldset1.='<h2 class="">Content</h2>';
$fieldset1.='</fieldset>';
$form->find('fieldset.submit')->before($fieldset1);
_u();

$fieldset2 = contentFields($content, $typeScheme, $typeFormTemplate);

$form->find('fieldset.submit')->before($fieldset2);

$webPage = webPageConstruct('Add Page');
$webPage->find('h1')->before(constructMenu('pagesAdd.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);*/