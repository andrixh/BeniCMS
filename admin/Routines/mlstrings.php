<?php
// MlStrings;
require_once('mlstrings.class.php');

function getLanguages(){
    $tempMl = new mlString('0');
    return mlString::$languages;
}

function getLanguages_ext(){
    $tempMl = new mlString('0');
	return mlString::$languages_ext;
}

function getMainLanguage(){
	$langs = getLanguages();
	return $langs[0];
}

function excerpt($string, $length=35){
	if (mb_strlen($string)>($length+2)){
		return mb_substr($string,0,$length).'&#8230;';
	}else{
		return $string;
	}
}