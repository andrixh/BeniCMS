<?php
function getImageWidth($physicalName){
	$return = 0;
    $query = Query::Select('images')->fields('width')->eq('physicalName',$physicalName);
	$result = DB::val($query);
	if ($result) {
		$return = $result;
	}
	return $return;
}

function getImageHeight($physicalName){
	$return = 0;
    $query = Query::Select('images')->fields('height')->eq('physicalName',$physicalName);
    $result = DB::val($query);
	if ($result) {
		$return = $result;
	}
	return $return;
}


function getPhysicalImageWidth($physicalName){
	$conformedDir = conf('IMAGE_CONFORMED_DIRECTORY');
	$result = 0;
	$originalFile = $conformedDir.$physicalName.'.jpg';
	if (is_file($originalFile)){
		list($width,$height)=getimagesize($originalFile);
		$result = $width;
	} 
	return $result;
}

function getPhysicalImageHeight($physicalName){
	$conformedDir = conf('IMAGE_CONFORMED_DIRECTORY');
	$result = 0;
	$originalFile = $conformedDir.$physicalName.'.jpg';
	if (is_file($originalFile)){
		list($width,$height)=getimagesize($originalFile);
		$result = $height;
	}
	return $result;
}

function resizeImage ($physicalName, $type, $requestedWidth = 0, $requestedHeight = 0, $quality= 50, $scaleMode='', $temp = false){
	_gc(__FUNCTION__);
	_d(getcwd(),'cwd');
	$conformedDir = conf('IMAGE_CONFORMED_DIRECTORY');
	$resizedDir = conf('IMAGE_RESIZED_DIRECTORY');
	
	_d($conformedDir,'$conformedDir');
	_d($resizedDir,'$resizedDir');
	if (strtoupper(substr($scaleMode,0,1)) == 'F') {
		$rgbColor = explode(',',$scaleMode);
		if (count($rgbColor)!=4){
			$rgbColor = array(0,0,0,0);
		}
	} else {
		$rgbColor = array(0,0,0,0);
	}
	$newImageName = normalizedImageName ($physicalName, $type, $requestedWidth, $requestedHeight, $quality, $scaleMode);
	list($srcX,$srxY,$srcW,$srcH,$dstX,$dstY,$dstW,$dstH,$newWidth,$newHeight) = calculateResizeCoords($physicalName,$requestedWidth,$requestedHeight,$scaleMode);
	if ($type == 'jpg') {
		$imageOriginal = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT'].$conformedDir.$physicalName.'.jpg'); //load original image
	} else {
		$imageOriginal = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].$conformedDir.$physicalName.'.png'); //load original image
	}
	$imageNew=imagecreatetruecolor($newWidth,$newHeight); //create new image canvas
	if ($type == 'png') {
		imagealphablending($imageNew,false);
		imagesavealpha($imageNew,true);
	} else {
		$color = imagecolorallocate($imageNew, $rgbColor[1], $rgbColor[2], $rgbColor[3]);
		imagefill($imageNew, 0, 0, $color);
	}
	imagecopyresampled($imageNew,$imageOriginal,$dstX,$dstY,$srcX,$srxY,$dstW,$dstH,$srcW,$srcH);//resample
	if ($temp==false){
		if ($type == 'png') {
			imagepng($imageNew,$_SERVER['DOCUMENT_ROOT'].$resizedDir.$newImageName, 9); //save png
		} else {
			imagejpeg($imageNew,$_SERVER['DOCUMENT_ROOT'].$resizedDir.$newImageName,$quality);//save jpg
		}
	} else {
		if ($type == 'png') {
			Header('Content-type: image/png');	
			return imagepng($imageNew,NULL,9);
			//imagepng($imageNew,$resizedDir.$newImageName, 9); //save png
		} else {
			Header('Content-type: image/jpeg');	
			return imagejpeg($imageNew,NULL,$quality);
			//imagejpeg($imageNew,$resizedDir.$newImageName,$quality);//save jpg
		}
	}
	_u();
	return true;
}

function calculateResizeCoords($physicalName, $requestedWidth, $requestedHeight, $requestedScaleMode){ //return $srcX,$srcY,$srcW,$srcH,$dstX,$dstY,$dstW, $dstH, $newWidth, $NewHeight
	$scaleMode = strtoupper(substr($requestedScaleMode,0,1));
	
	if (!(($scaleMode== 'C') || ($scaleMode== 'S') || ($scaleMode== 'F') || ($scaleMode== 'B'))) { // if wrong or no Scalemode specified, default to S-Stretch
		$scaleMode = 'S';
	}

	$originalWidth = getImageWidth($physicalName);
	$originalHeight = getImageHeight($physicalName);

	if (($requestedWidth == 0) && ($requestedHeight == 0)){ // no resize happening, regardless of quality request - display original image;
		$srcX = 0;
		$srxY = 0;
		$srcW = $originalWidth;
		$srcH = $originalHeight;
		$dstX = 0;
		$dstY = 0;				
		$dstW = $originalWidth;
		$dstH = $originalHeight;
		$newWidth = $originalWidth;
		$newHeight = $originalHeight;
	} else {
		if (($requestedWidth != 0) && ($requestedHeight == 0)){ //resize to requested width
			$srcX = 0;
			$srxY = 0;
			$srcW = $originalWidth;
			$srcH = $originalHeight;
			$dstX = 0;
			$dstY = 0;				
			$dstW = $requestedWidth;
			$dstH = round(($originalHeight/$originalWidth)*$requestedWidth);
			$newWidth = $dstW;
			$newHeight = $dstH;
		}
		if (($requestedWidth == 0) && ($requestedHeight != 0)){ //resize to requested height
			$srcX = 0;
			$srxY = 0;
			$srcW = $originalWidth;
			$srcH = $originalHeight;
			$dstX = 0;
			$dstY = 0;
			$dstW = round(($originalWidth/$originalHeight)*$requestedHeight);
			$dstH = $requestedHeight;
			$newWidth = $dstW;
			$newHeight = $dstH;
		}
		if (($requestedWidth != 0) && ($requestedHeight != 0)){ //resize to requested height and width accorting to scalemode
			$srcRatio = $originalWidth/$originalHeight;
			$dstRatio = $requestedWidth/$requestedHeight;
			if ($scaleMode == 'S') { //stretch
				//echo 'STRETCH';
				$srcX = 0;
				$srxY = 0;
				$srcW = $originalWidth;
				$srcH = $originalHeight;				
				$dstX = 0;
				$dstY = 0;
				$dstW = $requestedWidth;
				$dstH = $requestedHeight;
				$newWidth = $requestedWidth;
				$newHeight = $requestedHeight;
			}
			if ($scaleMode == 'C') { //crop
				if ($srcRatio > $dstRatio) { // crop sides
					//echo 'CROP SIDES';
					$srcX = round(($originalWidth-(($requestedWidth/$requestedHeight)*$originalHeight))/2);//round(($originalWidth-$requestedWidth)/2);
					$srxY = 0;						
					$srcW = round(($requestedWidth/$requestedHeight)*$originalHeight);// $requestedWidth; //?($originalWidth/$originalHeight)*$requestedHeight
					$srcH = $originalHeight;
					$dstX = 0;
					$dstY = 0;
					$dstW = $requestedWidth;
					$dstH = $requestedHeight;
				} else { // crop top and bottom
					//echo 'CROP TOP AND BOOTOM';
					$srcX = 0;
					$srxY = round(($originalHeight-(($requestedHeight/$requestedWidth)*$originalWidth))/2);//round(($originalHeight-$requestedHeight)/2);
					$srcW = $originalWidth;
					$srcH = round(($requestedHeight/$requestedWidth)*$originalWidth);//$requestedHeight; //?
					$dstX = 0;
					$dstY = 0;
					$dstW = $requestedWidth;
					$dstH = $requestedHeight;
				}
			$newWidth = $requestedWidth;
			$newHeight = $requestedHeight;
			}
			if ($scaleMode== 'F'){ //fill					
				//breakdown scalemode into RGB
				//$rgbColor = explode(',',$scaleMode);
				if ($srcRatio > $dstRatio) { //fit width
					//echo 'FIT WIDTH';
					$srcX = 0;
					$srxY = 0;
					$srcW = $originalWidth;
					$srcH = $originalHeight;
					$dstX = 0;
					$dstY = round(($requestedHeight - ($originalHeight/$originalWidth)*$requestedWidth)/2);
					$dstW = $requestedWidth;
					$dstH = round(($originalHeight/$originalWidth)*$requestedWidth);
				} else { //fit height
					//echo 'FIT HEIGHT';
					$srcX = 0;
					$srxY = 0;
					$srcW = $originalWidth;
					$srcH = $originalHeight;
					$dstX = round(($requestedWidth - ($originalWidth/$originalHeight)*$requestedHeight)/2);
					$dstY = 0;
					$dstW = round(($originalWidth/$originalHeight)*$requestedHeight);
					$dstH = $requestedHeight;
				}
			$newWidth = $requestedWidth;
			$newHeight = $requestedHeight;
			}
			if ($scaleMode== 'B'){ //fill					
				//breakdown scalemode into RGB
				//$rgbColor = explode(',',$scaleMode);
				if ($srcRatio > $dstRatio) { //fit width
					//echo 'FIT WIDTH';
					$srcX = 0;
					$srxY = 0;
					$srcW = $originalWidth;
					$srcH = $originalHeight;
					$dstX = 0;
					$dstY = 0;
					$dstW = $requestedWidth;
					$dstH = round(($originalHeight/$originalWidth)*$requestedWidth);
				} else { //fit height
					//echo 'FIT HEIGHT';
					$srcX = 0;
					$srxY = 0;
					$srcW = $originalWidth;
					$srcH = $originalHeight;
					$dstX = 0;
					$dstY = 0;
					$dstW = round(($originalWidth/$originalHeight)*$requestedHeight);
					$dstH = $requestedHeight;
				}
			$newWidth = $dstW;
			$newHeight = $dstH;
			}
		}
	}
return array($srcX,$srxY,$srcW,$srcH,$dstX,$dstY,$dstW,$dstH,$newWidth,$newHeight);
}

function normalizedImageName ($physicalName, $type, $requestedWidth, $requestedHeight, $quality, $scaleMode){
	//_gc(__FUNCTION__);
	if ($type == ''){
        $type = DB::val(Query::Select('images')->fields('type')->eq('physicalName',$physicalName));
	}
	list($srcX,$srxY,$srcW,$srcH,$dstX,$dstY,$dstW,$dstH,$newWidth,$newHeight) = calculateResizeCoords($physicalName,$requestedWidth,$requestedHeight,$scaleMode);
	$normalizedScaleMode = $scaleMode;
	if (($scaleMode == 'B')||($scaleMode == '')){
		$normalizedScaleMode = 'S';
	}
	//if ($type == 'jpg'){
		$normaizedquality = $quality;
	//} else {
		//$normaizedquality = 9;
	//}
	$result=$physicalName.'_'.$newWidth.'_'.$newHeight.'_'.$normalizedScaleMode.'_'.$normaizedquality.'.'.$type;
	
	return $result;
}

function getImage($physicalName, $requestedWidth = 0, $requestedHeight = 0, $quality= 50, $scaleMode='', $extraParams = ''){  

	$conformedDir = conf('IMAGE_CONFORMED_DIRECTORY');
	$resizedDir = conf('IMAGE_RESIZED_DIRECTORY');
	
	if (!((strtoupper(substr($scaleMode,0,1)) == 'C') || (strtoupper(substr($scaleMode,0,1)) == 'S') || (strtoupper(substr($scaleMode,0,1)) == 'F') || (strtoupper(substr($scaleMode,0,1)) == 'B'))) { // if wrong or no Scalemode specified, default to S-Stretch
		$scaleMode = 'S';
	}
	//$rgbColor = array('',0,0,0);
	$return = ''; //no return if failure
	$details = DB::row(Query::Select('images')->fields('label','type','description')->eq('physicalName',$physicalName));
	$type = $details->type;
	$originalFile = $conformedDir.$physicalName.'.'.$type;
	$normalizedImageName = normalizedImageName($physicalName,$type,$requestedWidth,$requestedHeight,$quality,$scaleMode);
	list($srcX,$srxY,$srcW,$srcH,$dstX,$dstY,$dstW,$dstH,$newWidth,$newHeight) = calculateResizeCoords($physicalName,$requestedWidth,$requestedHeight,$scaleMode);
	if (!is_file($resizedDir.$normalizedImageName)){
		resizeImage($physicalName,$type,$requestedWidth,$requestedHeight,$quality,$scaleMode);
	} 
	$return = '<img src="'.$resizedDir.$normalizedImageName.'" width="'.$newWidth.'" height="'.$newHeight.'" alt="'.$details->label.'" '.$extraParams.' />';
	return $return;
}
		

function deleteResizedImagesCache($physicalName=''){
	_gc(__FUNCTION__);
	_gc('parameters');_d($physicalName,'$physicalName');_u();
	$resizedDirectory = $_SERVER['DOCUMENT_ROOT'].conf('IMAGE_RESIZED_DIRECTORY');
	$allFiles = listFiles($resizedDirectory);
	//_d($allFiles,'allFiles');
	//echo $allFiles;
	if ($physicalName != ''){
		$pNameChunks = explode('.',$physicalName);
		foreach ($allFiles as $file) {
			$filechunks = explode('_',$file);
			_d($filechunks);
			if ($filechunks[0]==$pNameChunks[0]){	
				_d('unlinking '.$resizedDirectory.$file);
				$result = unlink($resizedDirectory.$file);
				_d($result,'unlink result');
			}
		}	
	}
	_u();
}



