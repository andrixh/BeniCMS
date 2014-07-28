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
require_once('Routines/validators.php');
require_once('Routines/mlstrings.class.php');
////


if (!isset($_POST['action'])){
	$_pageStructData = '';
	$_pageStruct = '';
	
	function createPageStruct(){
		global $_pageStruct;
		global $_pageStructData;
		$pages = DB::get(Query::Select('pages')->fields('ID','pageID','type','main','parent','rep','menuGroups','title','menuTitle','link','rank','active','track','cache')->desc('main')->asc('rank'));
        if (!$pages){
            setError('There are no pages present. Please chose a page type to create.',0,false);
            redirect('pagesAdd.php');
        }
		foreach ($pages as $page) {
			$_pageStruct[$page->pageID]= $page->parent;
			$_pageStructData[$page->pageID] = $page;
			$titleMl = mlString::Create($page->title);
			$menuTitleMl = mlString::Create($page->menuTitle);
			$_pageStructData[$page->pageID]->title = $titleMl->defaultValue();
			$_pageStructData[$page->pageID]->menuTitle = $menuTitleMl->defaultValue();
            $_pageStructData[$page->pageID]->menuGroup1 = (bool)($page->menuGroups & 1);
            $_pageStructData[$page->pageID]->menuGroup2 = (bool)($page->menuGroups & 2);
            $_pageStructData[$page->pageID]->menuGroup3 = (bool)($page->menuGroups & 4);
		}
		_d($_pageStructData,'Page Struct Data');
	}
	
	function getPageStruct(){
		global $_pageStruct;
		if ($_pageStruct == ''){
			createPageStruct();
		}
		return $_pageStruct;
	}
	
	function getPageStructData($pageID=''){
		global $_pageStructData;
		if ($_pageStructData == ''){
			createPageStruct();
		}
		if ($pageID == ''){
			return $_pageStructData;
		} else {
			return $_pageStructData[$pageID];
		}
	}
	
	function getParents($pageID){
		_gc(__FUNCTION__);
		$result = array();
		$pages = getPageStruct();
		if ($pages[$pageID]!=''){
			$par = $pageID;
			while ($par != ''){
				$par = $pages[$par];
				$result[]=$par;
			}
		}
		_d($result,$result);
		_u();
		return $result;
	}
	
	function getChildren($parent=''){
		$nodes = getPageStructData();
		$result = array();
		foreach ($nodes as $node){
			if ($node->parent == $parent) {
                $result[]=$node;
			}
		}
		if (count($result)==0){
			$result = false;
		}
		return $result;
	}

    /*function generateTree($startNode = '', $purpose=''){
        _gc('generateTree');
        _gc('parameters');_d($startNode,'$startNode');_d($purpose,'$purpose');_u();
        $result = '';
        $filters = array('main'=>'inMainMenu','side'=>'inSideMenu','footer'=>'inFooter');
        if ($purpose == ''){
            $filter = '';
        } else {
            $filter = $filters[$purpose];
        }
        $structData = getPageStructData();

        $children = getChildren($startNode, $filter);
        _d($children);
        if ($children){
            $result.= '<ul>';
            foreach ($children as $child){
                $result.='<li pageid="'.$child->pageID.'">'.generateTree($child->pageID).'</li>';
            }
            $result.= '</ul>';
        }
        _u();
        return $result;
    }*/

	function generateTree($startNode =''){
		_gc('generateTree');
		_gc('parameters');_d($startNode,'$startNode');_u();
		$result = '';
		$structData = getPageStructData();
		
		$children = getChildren($startNode);
		_d($children);
		if ($children){
			$result.= '<ul>';
			foreach ($children as $child){
				$result.='<li pageid="'.$child->pageID.'">'.generateTree($child->pageID).'</li>';
			}
			$result.= '</ul>';
		}
		_u();
		return $result;
	}
	
	require_script('Scripts/pageStruct.js');

	$webPage = webPageConstruct('View Site Structure');
	$webPage->find('h1')->before(constructMenu('pagesView.php'));
	$webPage->find('h1')->after(generateMessageBar());
	
	$pqTree = phpQuery::newDocument(generateTree());
	$pqTree->find('ul')->eq(0)->addClass('pageStruct');
	$pPages = pq('li');
	foreach($pPages as $pPage) {
		$pageID = pq($pPage)->attr('pageid');
		_d($pageID);
		$pageData = getPageStructData($pageID);
		$typeData = DB::row(Query::Select('pagetypes')->fields('label','typeID','icon')->eq('typeID', $pageData->type));
		$pageData->type=$typeData->typeID;
		$pageData->icon=$typeData->icon;
		_d($pageData);
		pq($pPage)->attr('data',json_encode($pageData));	
	}
	
	$webPage->append($pqTree);
	
	echo outputWebPage($webPage);	
} else {
	return ($_POST['action']);
	
}

