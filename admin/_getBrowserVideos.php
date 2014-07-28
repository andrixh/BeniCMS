<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');


$rawVideos = DB::get(Query::Select('videos')->fields('ID','physicalName','label','service','videoID','thumbnail'));

$imagesPath = conf('IMAGE_RESIZED_DIRECTORY');

$metadata = ['imagePath'=>$imagesPath];

$videos = [];
if ($rawVideos){
	foreach ($rawVideos as $rawVideo){
		$video = clone($rawVideo);
		$thumbdata = json_decode($rawVideo->thumbnail);
		$video->thumbnail = $thumbdata[0]->physicalName;
		$video->thumbnailType = $thumbdata[0]->type;
		$video->service = $rawVideo->service;
		$video->videoID = $rawVideo->videoID;
		$videos[]=$video;
	}
}

echo json_encode(['metadata'=>$metadata,'contents'=>$videos]);