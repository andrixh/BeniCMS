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

$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];

setError('Unknown Exception - No action performed.',2);

if (($_GET['id'])){
	$id = intval($_GET['id']);
	$currentRecord =DB::row(Query::Select('pages')->fields('pageID','type','main','parent','rank','rep','menuGroups','active','track','title','menuTitle')->eq('id', $id));
	$pageID=$currentRecord->pageID;
	$type=$currentRecord->type;
	$typeData = DB::row(Query::Select('pagetypes')->fields('scheme','formTemplate')->eq('typeID', $type));
	$typeScheme = json_decode($typeData->scheme);
	$typeFormTemplate = $typeData->formTemplate;
	setCount('pagetype',$type,-1);
	$main=$currentRecord->main;
	$parent=$currentRecord->parent;
	$rank=$currentRecord->rank;
	$rep=$currentRecord->rep;
	$menuGroups=$currentRecord->menuGroups;
	$active=$currentRecord->active;
	$track=$currentRecord->track;
	$title=mlString::Create($currentRecord->title);
	$menuTitle=mlString::Create($currentRecord->menuTitle);

	//$content=pageContentEstivate(json_decode($currentRecord->content,true), $typeScheme);
	//countContentResources($content,$typeScheme,-1);

	if (!$currentRecord) { 
		setError('Cannot find page with ID='.$id.' or Database Error.',2);
	}else{
		if ($main){
			setError('Page "'.$pageID.'" is set as the home page and cannot be deleted.',2); //TODO Mlstring delete	
		} else {
            $contentTable = 'pages_'.strtolower($type);
            $contentRecord = DB::row(Query::Select($contentTable)->eq('pageID',$pageID),DB::ASSOC);

            $content = pageContentEstivate($contentRecord, $typeScheme);
            countContentResources($content,$typeScheme,-1);


			$result = DB::query(Query::Delete('pages')->eq('id', $id));
			if (!$result) {
				setError('Cannot find page with ID='.$id.' or Database Error.',2);
			}else{
				$mlStrings = pageContentExtractMlStrings($content,$typeScheme);
				DB::query(Query::Delete($contentTable)->id($contentRecord['ID']));
                foreach ($mlStrings as $mlString){
					$mlString->delete();
				}
				$title->delete();
				$menuTitle->delete();

				commitCounts();
				//if ($parent != ''){
				DB::query(Query::Update('pages')->pairs(array('parent'=>$parent))->eq('parent',$pageID));
				//}
                DB::query(Query::Update('pages')->pairs(array('rep'=>''))->eq('rep',$pageID));
                DB::query(Query::Update('pages')->pairs(array('link'=>''))->eq('link',$pageID));
				setError('Page "'.$pageID.'" deleted.',0);
			}
		}
	}	
	
}
redirect($returnURL);
