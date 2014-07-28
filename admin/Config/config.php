<?php
$_conf = include($_SERVER['DOCUMENT_ROOT'].'/Config/conf.php');
$_privateconf = include($_SERVER['DOCUMENT_ROOT'].'/Config/privateconf.php');


//var_dump($conf);
//var_dump($privateconf);
if (preg_match('/\.local(host)?$/',$_SERVER['SERVER_NAME'])!==0) {
    $pcd = include($_SERVER['DOCUMENT_ROOT'].'/Config/privateconf_DEV.php');
    $_privateconf = array_merge($_privateconf,$pcd);
    unset($pcd);
}
//var_dump($privateconf);


function conf($key){
	global $_conf;
	global $_privateconf;
	if (isset($_conf[$key])) {
		return($_conf[$key]);
	} else if (isset($_privateconf[$key])) {
		return($_privateconf[$key]);
	} else {
		return NULL;
	}
}

