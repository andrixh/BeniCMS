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
require_once('Routines/mlstrings.class.php');
require_once('Routines/filesystem.php');
require_once('Routines/content.php');


require_table('videos');

$id=-1;
$returnURL = $_SERVER['HTTP_REFERER'];

setError('Unknown Exception - No action performed.',2);


if (($_GET['id'])){
	$id = intval($_GET['id']);
	$currentRecord =DB::row(Query::Select('videos')->fields('videoID','description','useCount','thumbnail','ownThumbnail')->eq('id', $id));

	if (!$currentRecord){
		setError('Cannot find video with ID='.$id.' or Database Error.',2);
	} else {
		$description = mlString::Create($currentRecord->description);
		$videoID = $currentRecord->videoID;
		$useCount = $currentRecord->useCount;
		$thumbnail = $currentRecord->thumbnail;
		$ownThumbnail = $currentRecord->ownThumbnail;

		if ($useCount > 0){
			setError('Video is in use and cannot be deleted.',2);
		} else {
			$query = Query::Delete('videos')->eq('ID', $id);
			DB::query($query);

			$description->delete();
			countGallery($thumbnail,-1);
			commitCounts();
			if ($ownThumbnail) {
				$thumbnailData = json_decode($thumbnail);
				_d($thumbnailData,'thumbnaildata');
				$thumbPname = $thumbnailData[0]->physicalName;
				$thumbData = DB::row(Query::Select('images')->fields('useCount','description','physicalName')->eq('physicalName',$thumbPname));
				if ($thumbData->useCount<=0){
					$desc = mlString::Create($thumbData->description)->delete();
					DB::query(Query::Delete('images')->eq( 'physicalName', $thumbData->physicalName));
					unlink($_SERVER['DOCUMENT_ROOT'].conf('IMAGE_CONFORMED_DIRECTORY').$thumbData->physicalName.'.jpg');
					$resizedFiles = glob($_SERVER['DOCUMENT_ROOT'].conf('IMAGE_RESIZED_DIRECTORY').$thumbData->physicalName.'*.*');
					array_map("unlink", $resizedFiles);

					$description=mlString::Create($thumbData->description)->delete();
				}

			}
			setError('Video deleted.',0);
		}
	}
}
redirect($returnURL);