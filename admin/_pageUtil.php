<?php 
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');
require_once('Includes/counter.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/form.inc.php');
require_once('Routines/validators.php');
require_once('Routines/filesystem.php');
require_once('Routines/mlstrings.php');
require_once('Routines/content.php');
require_once('Routines/selectProviders.php');
require_once('Includes/form.inc.php');

if ($_GET['action'] == ''){

} else if ($_GET['action'] == 'parent'){
	if (isset($_GET['child']) && $_GET['child']!='' && isset($_GET['parent'])){
		$parent = $_GET['parent'];
		$child = $_GET['child'];
        DB::query(Query::Update('pages')->pairs(['parent'=>$parent])->eq('pageID',$child));
	}
} else if ($_GET['action'] == 'rerank'){
	if (isset($_GET['ids']) && $_GET['ids']!='' && isset($_GET['ranks']) && $_GET['ranks']!=''){
		$ranks = explode('|', $_GET['ranks']);
		$ids = explode('|', $_GET['ids']);
		if (count($ranks) == count($ids)){
			for ($i = 0; $i<count($ranks); $i++){
				$ranks[$i] =intval($ranks[$i]);
                DB::query(Query::Update('pages')->pairs(['rank'=>$ranks[$i]])->eq('pageID',$ids[$i]));
			}
		}
	}
} else if ($_GET['action'] == 'represent'){
	if (isset($_GET['src']) && $_GET['src']!='' && isset($_GET['dst'])){
		$src = $_GET['src'];
		$dst = $_GET['dst'];
		
        DB::query(Query::Update('pages')->pairs(['rep'=>$dst])->eq('pageID',$src));
    }
} else if ($_GET['action'] == 'link'){
	if (isset($_GET['src']) && $_GET['src']!='' && isset($_GET['dst'])){
		$src = $_GET['src'];
		$dst = $_GET['dst'];
		
        DB::query(Query::Update('pages')->pairs(['link'=>$dst])->eq('pageID',$src));

	}
} else if (in_array($_GET['action'], ['active','track','cache'])){
    if (isset($_GET['src']) && $_GET['src']!='' && isset($_GET['val'])){
        $action = $_GET['action'];
        $src = $_GET['src'];
        $val = ($_GET['val'] == '0')?'0':'1';

        DB::query(Query::Update('pages')->pairs([$action=>$val])->eq('pageID',$src));

    }
} else if ($_GET['action'] == 'menuGroups'){
    if (isset($_GET['src']) && $_GET['src']!='' && isset($_GET['val'])){
        $action = $_GET['action'];
        $src = $_GET['src'];
        $val = $_GET['val'];

        DB::query(Query::Update('pages')->pairs(['menuGroups'=>$val])->eq('pageID',$src));

    }
} else if ($_GET['action'] == 'main'){
	if (isset($_GET['src']) && $_GET['src']!='' && isset($_GET['val'])){
		$src = $_GET['src'];
		$val = ($_GET['val'] == '0')?'0':'1';
		
        DB::query(Query::Update('pages')->pairs(['main'=>0]));
		
		if ($val == 1){
            DB::query(Query::Update('pages')->pairs(['main'=>1])->eq('pageID',$src));
		}
	}
	
} else if ($_GET['action'] == 'create') {
    $type = $_GET['type'];

    $return = isset($_GET['return'])?$_GET['return']:false;

    $pageIDS = DB::col(Query::Select('pages')->fields('pageID'));

    $i = 1;
    while (in_array('page'.$i,$pageIDS)){
        $i++;
    }
    $pageID = 'page'.$i;

    $titleMlString = MlString::Create()->defaultValue($pageID);
    $menuTitleMlString = MlString::Create()->defaultValue($pageID);

    $PageRecordPairs = [
        'pageID' => $pageID,
        'type' => $type,
        'main' => 0,
        'parent' => '',
        'rank' => 0,
        'rep' => '',
        'menuGroups' => 0,
        'active' => 0,
        'link' => '',
        'track' => 0,
        'title' => $titleMlString->strID,
        'menuTitle' => $menuTitleMlString->strID,
        'cache' => 0
    ];

    DB::query(Query::Insert('pages')->pairs($PageRecordPairs));
    $pageInsertID = DB::insert_id();

    $titleMlString->usedTable('pages')->usedID($pageInsertID)->save();
    $menuTitleMlString->usedTable('pages')->usedID($pageInsertID)->save();

    $pageTable = 'pages_'.$type;
    DB::query(Query::Insert($pageTable)->pairs(['pageID'=>$pageID]));

    $result = DB::row(Query::Select('pages')->fields('ID','pageID','type','main','parent','rep','menuGroups','title','menuTitle','link','rank','active','track','cache')->id($pageInsertID),DB::ASSOC);

    $typeData = DB::row(Query::Select('pagetypes')->fields('label','icon')->eq('typeID', $type));

    $result['icon'] = $typeData->icon;
    $result['title'] = $titleMlString->defaultValue();
    $result['menuTitle'] = $menuTitleMlString->defaultValue();
    $result['menuGroup1'] = 0;
    $result['menuGroup2'] = 0;
    $result['menuGroup3'] = 0;

    if ($return) {
        redirect('pagesView.php');
    } else {
        echo json_encode($result);
    }
    die();
} else if ($_GET['action'] == 'setID'){
    $prevID = $_GET['prevID'];
    $newID = str_replace(' ','_',$_GET['newID']);
    $pageRecord = DB::row(Query::Select('pages')->fields('ID','type','pageID')->eq('pageID',$prevID));
    if (!$pageRecord) {
        die('');
    }

    $existingPage = DB::row(Query::Select('pages')->fields('ID')->eq('pageID',$newID));

    $type = $pageRecord->type;
    $result = '';
    if (!$existingPage || $existingPage->ID != $pageRecord->ID) {
        if (!(strlen($newID) < 1 || strlen($newID) > 255 || !allowedChars($newID,conf('ALPHANUM').'-_') || !allowedChars(substr($newID,0,1),conf('ALPHA')))){
            DB::query(Query::Update('pages')->pairs(['pageID'=>$newID])->id($pageRecord->ID));
            DB::query(Query::Update('pages_'.$pageRecord->type)->pairs(['pageID'=>$newID])->eq('pageID',$prevID));
            DB::query(Query::Update('pages')->pairs(['parent'=>$newID])->eq('parent',$prevID));
            die($newID);
        }
    }
    die('');
} else if ($_GET['action'] == 'getDescriptionForm') {
    $result = '<input type="hidden" class="hidden" name="pageID"/>';
    $result .= field(label('Title'),control_mlTextInput('title', mlString::Create()->getValues()));
    $result .= field(label('Menu Title'),control_mlTextInput('menuTitle', mlString::Create()->getValues()));
    echo $result;
} else if ($_GET['action'] == 'getDescription') {
    $pageRecord = DB::row(Query::Select('pages')->fields(['title','menuTitle'])->eq('pageID',$_GET['pageID']));
    $titleMlString = mlString::Create($pageRecord->title);
    $menuTitleMlString = mlString::Create($pageRecord->menuTitle);

    echo json_encode([$titleMlString->getValues(),$menuTitleMlString->getValues()]);
} else if ($_GET['action'] == 'setDescription') {
    $pageID = $_POST['pageID'];

    $pageRecord = DB::row(Query::Select('pages')->fields(['title','menuTitle'])->eq('pageID',$pageID));

    $titleMlString = mlString::Create($pageRecord->title)->fromPost('title');
    $menuTitleMlString = mlString::Create($pageRecord->menuTitle)->fromPost('menuTitle');
    $titleMlString->save();
    $menuTitleMlString->save();

    die(json_encode([$titleMlString->defaultValue(),$menuTitleMlString->defaultValue()]));
}
