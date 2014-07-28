<?php
namespace Page;

use Request;
use Response;
use Config;
use Mimes;

class MissingAsset extends \SystemPage {



    public function output(){
        $req = explode('.',implode('/',Request::getInstance()->path));

        $file = $req[0];
        $ext = $req[1];

        if (!in_array($ext,array('jpg','png','gif','eot','woff','svg','ttf','otf'))){
            $this->fail('unsupported type');
        }

        $sourceFileNeme = Config::get('path.assets.source').'/'.$file.'.'.$ext;
        if (file_exists($sourceFileNeme)){
            $destFileName = Config::get('path.assets.output').'/'.$file.'.'.$ext;
            if (!file_exists(dirname($destFileName)) || !is_dir(dirname($destFileName))){
                mkdir(dirname($destFileName), 0755, true);
            }

            if (\Env::isProd()){
                copy($sourceFileNeme,$destFileName);
            }
            Response::setHeader(Response::HEADER_CONTENT_TYPE,Mimes::getType(strtolower($ext)));
            Response::Output(file_get_contents($sourceFileNeme));
            die();
        }

        $this->fail('unspecified error');

    }


    protected function fail($reason,$label=''){
        _d($reason,$label);
        Response::setStatus(Response::STATUS_404);
        Response::Output('Asset not found');
        die();
    }
}