<?php
namespace Turbina\Assets\Controller;

use Turbina\Core\Config;
use Turbina\Core\ControllerPrototype;
use Turbina\Core\Debug;
use Turbina\Core\Mimes;
use Turbina\Core\Response;

class MainController extends ControllerPrototype{

    public function missingGfxAction($bundle,$file,$ext){
        $sourceFileNeme = str_replace('{bundle}',$bundle,Config::get('path.assets.source')).'/'.$file.'.'.$ext;
        if (file_exists($sourceFileNeme)){
            $destFileName = str_replace('{bundle}',$bundle,Config::get('path.assets.output')).'/'.$file.'.'.$ext;
            if (!file_exists(dirname($destFileName)) || !is_dir(dirname($destFileName))){

                mkdir(dirname($destFileName), 0755, true);
            }

            if (copy($sourceFileNeme,$destFileName)){
                Response::setHeader(Response::HEADER_CONTENT_TYPE,Mimes::getType(strtolower($ext)));
                return file_get_contents($destFileName);
            }

        }

        Response::setStatus(Response::STATUS_404);

        return '404 Not found';
    }
}