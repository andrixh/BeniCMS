<?php
function decodeVideoUrl($url){
	$result = false;
	$service = '';
	$videoID = '';

	if (strstr(strtolower($url), 'youtube.com')!== false){
		$service = 'youtube';
		//$pattern = '/.*v=([A-Za-z0-9_]+).*?/';
		//$matches = array();
		//preg_match($pattern, $url, $matches);

		parse_str(parse_url($url, PHP_URL_QUERY), $variables);
		$videoID = $variables['v'];
		//$videoID = $matches[1];
		$result = array($service,$videoID);
	} elseif (strstr(strtolower($url), 'vimeo.com')!== false){
		$service = 'vimeo';
		$pattern = '/.*\/([0-9]+).*?/';
		$matches = array();
		preg_match($pattern, $url, $matches);
		_d($matches);
		$videoID = $matches[1];
		$result = array($service,$videoID);
	} elseif (strstr(strtolower($url), 'dailymotion.com')!== false){
		$service = 'dailymotion';
		$pattern = '/.*video\/([A-Za-z0-9]+).*?/';
		preg_match($pattern, $url, $matches);
		_d($matches);
		$videoID = $matches[1];
		$result = array($service,$videoID);
	}
	return $result;
}

function getVideoData($service,$videoID){
	if ($service == 'youtube'){
		return getYoutubeData($videoID);
	} else if ($service == 'vimeo') {
		return getVimeoData($videoID);
	} else if ($service == 'dailymotion'){
		return getDailyMotionData($videoID);
	} else {
		return false;
	}
}

function getYoutubeData($videoID){
	$result = array();
	$xmlData = simplexml_load_string(file_get_contents("http://gdata.youtube.com/feeds/api/videos/{$videoID}?fields=title"));
	$title = (string)$xmlData->title;
	$imageUrl = 'http://img.youtube.com/vi/'.$videoID.'/0.jpg';
	$result['title']=$title;
	$result['thumbnail']=$imageUrl;
	return $result;
}

function getVimeoData($videoID){
	$result = array();
	$jsonurl = 'http://vimeo.com/api/v2/video/'.$videoID.'.json';
	$jsonData = json_decode(file_get_contents($jsonurl));
	_d($jsonData,'JSON data');
	$title = $jsonData[0]->title;
	$imageUrl = $jsonData[0]->thumbnail_large;

	$result['title']=$title;
	$result['thumbnail']=$imageUrl;
	return $result;
}

function getDailyMotionData($videoID){
	$result = array();
	$imageUrl = 'http://www.dailymotion.com/thumbnail/video/'.$videoID;
	$jsonData = json_decode(file_get_contents("http://www.dailymotion.com/services/oembed?format=json&url=http://www.dailymotion.com/embed/video/$videoID"), true);
	_d($jsonData);
	$title = $jsonData['title'];
	$result['title']=$title;
	$result['thumbnail']=$imageUrl;
	return $result;
}