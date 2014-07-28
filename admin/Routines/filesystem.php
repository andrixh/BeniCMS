<?php
function listFiles($dir, $ext='*'){
	_gc('listFiles');
	_t();
	$result = array();
	$handler = opendir($dir);
	while ($file = readdir($handler)) {
		_d($file);
		if ($file != '.' && $file != '..' && (is_dir($dir.'/'.$file)==false)){
			if ($ext=='*'){
				$result[] = $file;
			} else {
				$pathinfo = pathinfo ($dir.'/'.$file);
				if (strtolower($pathinfo['extension']) == strtolower($ext)){
					$result[] = $file;	
				}
			}
		}	
	}
	closedir($handler);
	_d($result,'result');
	_u();
	return $result; 
}

function provide_files($dir,$ext){
	_gc('Provide_Files');
	_t();
	$result=array();
	$files = listFiles($dir,$ext);
	_d($files,'files found');
	foreach ($files as $file) {
		$pathinfo=pathinfo($file);
		$result[$pathinfo['filename']]=$dir.'/'.$file;
	}
	_d($result,'result');
	_u();
	return $result;
}


function generateFileName ($length = 20){
	$length=20;
	$result = uniqid();
	$possible = "0123456789abcdefghijklmnopqrstuvwxyz";
	$start =strlen($result)-1;
	for($i= $start; $i<$length; $i++) { 
	  	$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);   
  		$result = $char.$result;
  	}
  	return $result;
}

function generateUniqueFileName ($directory, $extension='', $length=20){
	$fileName = generateFileName($length);
	$ext = '';
	if ($extension != ''){
		$ext = '.'.$extension;
	}
	while (file_exists($directory.$fileName.$ext)){
		$fileName = generateFileName($length);		
	}
	return $fileName;
}