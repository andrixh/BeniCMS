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


if (!isset($_GET['type']) && !isset($_POST['type'])){
    setError('Cannot find type of content!',2);
    redirect($_SERVER['HTTP_REFERER']);
} else {
    $type = $_GET['type'];
    if (isset($_POST['type'])){
        $type=$_POST['type'];
    }
}

$tableName = 'contents_'.$type;
$scheme = json_decode(DB::val(Query::Select('contenttypes')->fields('scheme')->eq('typeID', $type)->limit(1)));
require_table($tableName);

$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];

setError('Unknown Exception - No action performed.',2);

if (($_GET['id'])){
    $typeData = DB::row(Query::Select('contenttypes')->fields('scheme','label','formTemplate')->eq('typeID', $type)->limit(1));
	$typeScheme = json_decode($typeData->scheme);

	
	$id = intval($_GET['id']);
	$currentRecord =DB::row(Query::Select($tableName)->eq('id', $id)->limit(1),DB::ASSOC);;

	setCount('contenttype',$type,-1);
   
   $content = array();
   
    foreach ($typeScheme as $typeField){
      if (array_search($typeField->type, getMlTypes())){
         $content[$typeField->name] = mlString::Create($currentRecord[$typeField->name])->postName('content_'.$typeField->name);
      } else {
         $content[$typeField->name] = $currentRecord[$typeField->name];
      }
   }
   
	//$content=pageContentEstivate(json_decode($currentRecord->content,true), $typeScheme);
	countContentResources($content,$typeScheme,-1);
	if (!$currentRecord) { 
		setError('Cannot find content with ID='.$id.' or Database Error.',2);
	}else{
		if ($currentRecord['useCount'] == 0){
			$result = DB::query(Query::Delete($tableName)->eq('id', $id));

			if (!$result) {
				setError('Cannot find '.ucfirst($type).' Content with ID='.$id.' or Database Error.',2);
			}else{
				$mlStrings = pageContentExtractMlStrings($content,$typeScheme);
				foreach ($mlStrings as $mlString){
					$mlString->delete();
				}
				commitCounts();
				setError(ucfirst($typeData->label).' content "'.$currentRecord['permalink'].'" Deleted',0);
			}
		} else {
			setError(ucfirst($typeData->label).' content "'.$currentRecord['permalink'].'" is in use and cannot be deleted',2);
		}
	}	
	
}
redirect($returnURL);
