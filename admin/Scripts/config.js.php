<?php
header('Content-Type:text/javascript; charset=UTF-8');
//print_r ($_SERVER);
//error_reporting(0);
chdir($_SERVER['DOCUMENT_ROOT'].'/admin');
require_once ($_SERVER['DOCUMENT_ROOT'].'/admin/Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Routines/mlstrings.php');

function collectConfig(){
	$result = array();
	global $_conf;
	foreach ($_conf as $key=>$value){
		//if (isset($value['publish']) && $value['publish'] == true ){
			$result[$key]=$value;
		//}
	}
	$result['LANGUAGES']=getLanguages_ext();
	
	return $result;
}

$out = 'var CONFIG='.json_encode(collectConfig()).';';

echo $out;