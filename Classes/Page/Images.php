<?php
namespace Page;

use Response;
use Request;
use Config;

class Images extends \SystemPage {

    protected $pname;
    protected $width;
    protected $height;
    protected $type;
    protected $scaleMode;
    protected $quality;

    protected $temp = false;

    protected $resizePath;
    protected $sourcePath;

    protected $sourceFile;
    protected $sourceWidth;
    protected $sourceHeight;

    protected $destFile;

    protected $resizeData;

    protected $resizedImage;

    public function prepare(){
        $this->validateRequest();
        $this->getSourceDimensions();
        $this->calculateResizeCoords();

        $this->destFile = $_SERVER['DOCUMENT_ROOT'].$this->resizePath.$this->pname.'_'.$this->resizeData['width'].'_'.$this->resizeData['height'].'_'.$this->scaleMode.'_'.$this->quality.'.'.$this->type;
    }


    public function output(){
        _d($this,'beforeoutput');
        if (!file_exists($this->destFile)){
            $this->resizeImage();
            if (!$this->temp){
                $this->saveimage();
            }
            if ($this->type == 'png') {
                Response::setHeader(Response::HEADER_CONTENT_TYPE,\Mimes::getType('png'));
                $output = imagepng($this->resizedImage,NULL,9);
            } else {
                Response::setHeader(Response::HEADER_CONTENT_TYPE,\Mimes::getType('jpg'));
                $output = imagejpeg($this->resizedImage,NULL,$this->quality);
            }
            Response::Output($output);
        } else {
            if ($this->type == 'png') {
                Response::setHeader(Response::HEADER_CONTENT_TYPE,\Mimes::getType('png'));
            } else {
                Response::setHeader(Response::HEADER_CONTENT_TYPE,\Mimes::getType('jpg'));
            }
            Response::Output(file_get_contents($this->destFile));
        }

    }

    protected function saveimage(){
        if ($this->type == 'png') {
            imagepng($this->resizedImage,$this->destFile, 9); //save png
        } else {
            imagejpeg($this->resizedImage,$this->destFile,$this->quality);//save jpg
        }
    }



    public function resizeImage (){
        $rgbColor = array(0,0,0,0);

        if ($this->type == 'jpg') {
            $imageOriginal = imagecreatefromjpeg($this->sourceFile); //load original image
        } else {
            $imageOriginal = imagecreatefrompng($this->sourceFile); //load original image
        }
        $imageNew=imagecreatetruecolor($this->resizeData['width'],$this->resizeData['height']); //create new image canvas
        if ($this->type == 'png') {
            imagealphablending($imageNew,false);
            imagesavealpha($imageNew,true);
            $color = imagecolorallocatealpha($imageNew, $rgbColor[0], $rgbColor[1], $rgbColor[2], 127);
            imagefill($imageNew, 0, 0, $color);
        } else {
            $color = imagecolorallocate($imageNew, $rgbColor[0], $rgbColor[1], $rgbColor[2]);
            imagefill($imageNew, 0, 0, $color);
        }
        imagecopyresampled($imageNew,$imageOriginal,$this->resizeData['dstX'],$this->resizeData['dstY'],$this->resizeData['srcX'],$this->resizeData['srxY'],$this->resizeData['dstW'],$this->resizeData['dstH'],$this->resizeData['srcW'],$this->resizeData['srcH']);//resample

        $this->resizedImage = $imageNew;
    }


    protected function validateRequest(){
        $req = Request::getInstance()->path;

        $this->resizePath = Config::get('IMAGE_RESIZED_DIRECTORY'); // "/Images/Resized/"
        $this->sourcePath = Config::get('IMAGE_CONFORMED_DIRECTORY'); //=>'/Images/Conformed/'

        if (count($req) < 2){
            $this->fail('not enough parameter parts');
        }

        $rszparts = explode(' ',trim(str_replace('/',' ',$this->resizePath)));
        array_shift($rszparts);
        $rszparts[] = $req[count($req)-1];

        if (implode('/',$rszparts) != (implode('/',$req))){

            $this->fail(array($rszparts,$req),'incorrect call parts');
        }
        $this->decodeParameters($req[count($req)-1]);
    }

    protected function decodeParameters($img){
        if (!is_string($img)){
            $this->fail('expected string');
        }

        $parts = explode('.',$img);
        if (count($parts)!= 2 && ($parts[1]!='jpg' || $parts[1]!='png')){
            $this->fail('malformed filename or wrong type');
        }

        $this->type = $parts[1];

        $params = explode('_',$parts[0]);
        if (count($params)<5 || count($params) >6){
            $this->fail(count($params),'wrong number of parameters');
        }

        if (count($params) == 6 && $params[5] != 't'){
            $this->fail($params[5],'temp parameter incorrect');
        }

        $this->sourceFile = $_SERVER['DOCUMENT_ROOT'].$this->sourcePath.$params[0].'.'.$this->type;
        _d($this->sourceFile,'source file' );
        if (!file_exists($this->sourceFile)) {
            $this->fail($this->sourceFile,'source not found');
        }

        $this->pname = $params[0];
        $this->width = (int)$params[1];
        $this->height = (int)$params[2];
        $this->scaleMode = $params[3];
        $this->quality = (int)$params[4];

        $this->temp = count($params) == 6 && $params[5] == 't';

        if ($this->width < 0 || $this->height < 0){
            $this->fail('negative dimensions');
        }


        $maxW = Config::get('IMAGE_CONFORM_WIDTH');
        $maxH = Config::get('IMAGE_CONFORM_HEIGHT');
        if ($this->width > $maxW) {
            if ($this->height > 0){
                $this->height = floor($this->height * $maxW / $this->width);
            }
            $this->width = $maxW;
            _d($this->width,'diminished width');
            _d($this->height,'diminished height');
        }

        if ($this->height > $maxH) {
            if ($this->width > 0){
                $this->width = floor($this->width * $maxH / $this->height);
            }
            $this->height = $maxH;
            _d($this->width,'diminished width');
            _d($this->height,'diminished height');
        }

        if (!in_array($this->scaleMode,array('S','C','B','F'))){
            $this->fail('wrong scale mode');
        }

        if ($this->quality < 0 || $this->quality > 100){
            $this->fail('wrong image quality');
        }
    }

    protected function getSourceDimensions(){
        list($width,$height)=getimagesize($this->sourceFile);
        if (!$width || !$height){
            $this->fail('could not get source image size');
        }
        $this->sourceWidth = $width;
        $this->sourceHeight = $height;

    }


    protected function fail($reason,$label=''){
        _d($reason,$label);
        Response::setStatus(Response::STATUS_404);
        Response::Output('Image not found');
        die();
    }

    function calculateResizeCoords(/*$physicalName, $requestedWidth, $requestedHeight, $requestedScaleMode*/){ //return $srcX,$srcY,$srcW,$srcH,$dstX,$dstY,$dstW, $dstH, $newWidth, $NewHeight
        $scaleMode = $this->scaleMode;

        $originalWidth = $this->sourceWidth;
        $originalHeight = $this->sourceHeight;

        $requestedWidth = $this->width;
        $requestedHeight = $this->height;


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
                if ($scaleMode== 'B'){ //best fit
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

        $this->resizeData = array(
            'srcX'=>$srcX,
            'srxY'=>$srxY,
            'srcW'=>$srcW,
            'srcH'=>$srcH,
            'dstX'=>$dstX,
            'dstY'=>$dstY,
            'dstW'=>$dstW,
            'dstH'=>$dstH,
            'width'=>$newWidth,
            'height'=>$newHeight
        );
    }

}

