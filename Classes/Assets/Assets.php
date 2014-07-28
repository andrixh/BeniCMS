<?php
namespace Assets;

use \Config;
//use Turbina\Core\Finder;
//use Turbina\Core\Router;


class Assets
{

    protected static $bundle = '';

    protected static $minify = false;
    protected static $combine = false;
    protected static $gzip = false;

    protected static $assets = [];
    protected static $dependencies = [];
    protected static $asset_order = [];


    protected static $output_prepared = false;

    protected static $requests = [];

    protected static $result_head = '';
    protected static $result_bottom = '';

    public static function __Init()
    {
        //Debug::group('ASSETS INITIALIZING');
        self::$combine = Config::get('assets.combine');
        self::$minify = Config::get('assets.minify');
        self::$gzip = Config::get('assets.gzip');

        //self::$bundle = Router::current()->getBundle();
        self::loadAssets();
        //Debug::groupClose();
    }

    public function getBundle(){
        return self::$bundle;
    }

    protected static function addAsset($assetDef){
        //Debug::groupCollapsed('addAsset '.$assetDef);
        $newAsset = new Asset($assetDef,self::$bundle);
        self::$assets[$newAsset->getName()] = $newAsset;

        //Debug::log($newAsset,'new asset');
        //Debug::groupClose();
        return $newAsset->getName();
    }

    protected static function loadAssets()
    {
        //Debug::group('Assets::loadAssets');
        $definitions = [];
        $definitionPath = Config::get('path.assets.definition');

        $definitionFiles = glob($definitionPath.'*.php');
        //Debug::log($definitionFiles,'definitions file');
        foreach ($definitionFiles as $definitionFile) {
            if (file_exists($definitionFile)) {
                $loadedDefinitions = include($definitionFile);
                $definitions = array_merge($definitions, $loadedDefinitions);
            }
        }
        //Debug::log($definitions,'definitions loaded from file');
        foreach ($definitions as $assetDef => $deps) {
            //Debug::group('adding asset');
            //Debug::log($assetDef,'assedDef');
            //Debug::log($deps,'deps');
            $assetName = self::addAsset($assetDef);
            if (count($deps) > 0) {
                if (array_key_exists($assetName, self::$dependencies)) {
                    self::$dependencies[$assetName] = array_merge(self::$dependencies[$assetName] . $deps);
                } else {
                    self::$dependencies[$assetName] = $deps;
                }
            }
            //Debug::groupClose();
        }
        //Debug::log(self::$assets,'assets');
        //Debug::log(self::$dependencies,'dependencies');
        //Debug::groupClose();
    }

    public static function require_asset($asset)
    {
        //Debug::group('Require_Asset: '.$asset);
        //Debug::stackTrace('st');
        //Debug::log($asset);
        self::$requests[] = $asset;
        if (!array_key_exists($asset, self::$assets)) {
            self::addAsset($asset);
        }
        //Debug::groupClose();
    }

    /**
     * Recursive
     *
     * @param $res
     * @return array
     */
    private static function calcDep($res)
    {

        $result = [$res];
        if (array_key_exists($res, self::$dependencies)) {
            foreach (self::$dependencies[$res] as $depList) {
                $result[] = $depList;
                $immediateDeps = self::calcDep($depList);
                foreach ($immediateDeps as $immediateDep) {
                    $result[] = $immediateDep;
                }
            }
        }
        return $result;
    }

    protected static function calculateDependencies()
    {
        $deplist = [];
        foreach (self::$requests as $req) {
            $deps = self::calcDep($req);
            foreach ($deps as $dep) {
                $deplist[] = $dep;
            }
        }
        self::$asset_order = array_unique(array_reverse($deplist));

        foreach (self::$asset_order as $assetName){
            if (!array_key_exists($assetName,self::$assets)){
                self::addAsset($assetName,self::$bundle);
            }
        }
        self::$asset_order = array_values(self::$asset_order);
    }


    protected static function prepareOutput()
    {
        //Debug::group();
        if (self::$output_prepared){
            return;
        }
        self::$output_prepared = true;
        self::calculateDependencies();

        //Debug::log(self::$asset_order,'Ordered assets');


        //Now that demendenices are calculated, it's time to do the heavy lifting:

        // have all assets find their source;
        foreach (self::$asset_order as $assetName){
            self::$assets[$assetName]->findSource();
        }


        //calculate what to place on the head, and what on the body
        //styles always go in the head


        $headStyles = [];
        $headScripts = [];
        $bottomScripts = [];

        //Debug::group('dividing assets by position');
        $assetCount = count(self::$asset_order);
        for ($i = $assetCount-1; $i >= 0 ; $i--) {
            //Debug::log($i);
            $assetName = self::$asset_order[$i];
            $asset = self::$assets[$assetName];

            if ($asset->getType() == Asset::STYLE){
               $headStyles[] = $assetName;
            } else if ($asset->getType() == Asset::SCRIPT) {
                if ($asset->inHead() || count($headScripts) > 0) {
                    $headScripts[] = $assetName;
                } else {
                    $bottomScripts[] = $assetName;
                }
            }
        }

        $headStyles = array_reverse($headStyles);
        $headScripts = array_reverse($headScripts);
        $bottomScripts = array_reverse($bottomScripts);

        //Debug::log($headStyles,'head styles');
        //Debug::log($headScripts,'head scripts');
        //Debug::log($bottomScripts,'bottom scripts');

        //Debug::groupClose();

        if (self::$combine) {

            self::$result_head= self::combineOutputGroups($headStyles,Asset::STYLE);
            self::$result_head.= self::combineOutputGroups($headScripts,Asset::SCRIPT);
            self::$result_bottom= self::combineOutputGroups($bottomScripts,Asset::SCRIPT);

        } else {
            self::$result_head= self::prepareUncombined($headStyles,Asset::STYLE);
            self::$result_head.= self::prepareUncombined($headScripts,Asset::SCRIPT);
            self::$result_bottom= self::prepareUncombined($bottomScripts,Asset::SCRIPT);
        }
        //Debug::log(self::$assets, 'full assets list');
        //Debug::groupClose();
    }


    protected static function prepareUncombined($assetList,$type){
        //Debug::group('prepare Uncombined Assets');
        if ($type == Asset::STYLE) {
            $outputPathTemplate = Config::get('path.assets.output').'/Styles/{filename}';
            $inlineTeplate = '<style>{contents}</style>';
            $declarationTemplate = '<link media="screen" type="text/css" rel="stylesheet" href="{assetName}"/>';
        } else {
            $outputPathTemplate = Config::get('path.assets.output').'/Scripts/{filename}';
            $inlineTeplate =  '<script type="text/javascript">{contents}</script>';
            $declarationTemplate = '<script type="text/javascript" src="{assetName}"></script>';
        }

        $result = '';

        foreach ($assetList as $assetName){
            //Debug::group($assetName);

            $asset = self::$assets[$assetName];
            //Debug::log($asset);
            if ($asset->isExternal()) {
                //Debug::log('external');
                $result.=str_replace('{assetName}',$assetName,$declarationTemplate);
                //$result.='<script type="text/javascript" src="'.$assetName.'"></script>';
            } else if ($asset->isInline()){
                //Debug::log('inline');
                $asset->load();
                if (self::$minify && $asset->canMinify()){
                    $asset->minify();
                }
                $result.= str_replace('{contents}',$asset->getData(),$inlineTeplate);
                //$result.='<script type="text/javascript">'.$asset->getData().'</script>';
            } else {
                //Debug::log('normal');
                $assetTime = $asset->getSourceTime();
                $assetPath = str_replace('{filename}',$assetName,$outputPathTemplate);
                //$assetPath = str_replace('{bundle}', self::$bundle, Config::get('assets.output.path')).'/Scripts/'.self::calculateCombinedName(array($assetName),$assetTime).'.js';
                //Debug::log(Config::get('assets.force_recompile'),"Config::get('assets.force_recompile')");
                if (!file_exists($assetPath) || filemtime($assetPath) < $assetTime || Config::get('assets.force_recompile')) {
                    $asset->load();
                    if (self::$minify && $asset->canMinify()){
                        $asset->minify();
                    }
                    self::writeFile($assetPath,$asset->getData());
                    if (self::$gzip) {
                        self::writeFile($assetPath.'.gz',gzencode($asset->getData())); //write the compressed version
                    }
                }
                $result.=str_replace('{assetName}',self::unroot(str_replace('{filename}',$assetName,$outputPathTemplate)),$declarationTemplate);
                //$result.= '<script type="text/javascript" src="'.self::unroot($assetPath).'"></script>'
            }
        }
        //Debug::groupClose();
        return $result;
    }

    protected static function combineOutputGroups($assetList,$type){
        //Debug::group('combineOutputGroups');
        //Debug::log(func_get_args(),'args');
        //assume asset type is SCRIPT
        $outputPathTemplate = Config::get('path.assets.output').'/Cache/{filename}.js';
        $inlineTeplate =  '<script type="text/javascript">{contents}</script>';
        $declarationTemplate = '<script type="text/javascript" src="{assetName}"></script>';
        if ($type == Asset::STYLE) {
            $outputPathTemplate = Config::get('path.assets.output').'/Cache/{filename}.css';
            $inlineTeplate = '<style>{contents}</style>';
            $declarationTemplate = '<link media="screen" type="text/css" rel="stylesheet" href="{assetName}"/>';
        }

        $result = '';

        $assetGroups = [];
        $groupIndex = 0;
        foreach ($assetList as $assetName) {
            $asset = self::$assets[$assetName];
            if ($asset->canCombine() && !$asset->isExternal() && !$asset->isInline()) {
                if (!isset($assetGroups[$groupIndex])){
                    $assetGroups[$groupIndex] = [];
                }
                $assetGroups[$groupIndex][] = $assetName;
            } else {
                $groupIndex++;
                $assetGroups[$groupIndex] = $assetName;
                $groupIndex++;
            }
        }

        //Debug::log($assetGroups,'asset groups');

        foreach ($assetGroups as $group){
            if (is_array($group)) {
                //Debug::log($group,'is array');
                $groupTime = 0;
                foreach ($group as $assetName) {
                    $assetTime = self::$assets[$assetName]->getSourceTime();
                    if ($assetTime > $groupTime){
                        $groupTime = $assetTime;
                    }
                }
                $groupFileName = self::calculateCombinedName($group,$groupTime);
                $assetPath = str_replace('{filename}',$groupFileName,$outputPathTemplate);

                if (!file_exists($assetPath)){
                    $contents = '';
                    foreach ($group as $assetName) {
                        self::$assets[$assetName]->load();
                        if (self::$minify && self::$assets[$assetName]->canMinify()) {
                            self::$assets[$assetName]->minify();
                        }
                        if (self::$assets[$assetName]->getType() == Asset::SCRIPT){
                            $separator = ";\n";
                        } else {
                            $separator = "\n";
                        }
                        $contents.= self::$assets[$assetName]->getData().$separator;
                    }

                    self::writeFile($assetPath,$contents); //write the uncompressed file
                    if (self::$gzip) {
                        self::writeFile($assetPath.'.gz',gzencode($contents)); //write the compressed version
                    }
                    self::cleanup($assetPath);
                }
                $result.=str_replace('{assetName}',self::unroot($assetPath),$declarationTemplate);

            } else if (is_string($group)) {
                //Debug::log($group,'is string');
                $asset = self::$assets[$group];
                $assetName = $asset->getName();
                if ($asset->isExternal()) {
                    $result.=str_replace('{assetName}',$assetName,$declarationTemplate);
                    //$result.='<script type="text/javascript" src="'.$assetName.'"></script>';
                } else if ($asset->isInline()){
                    $asset->load();
                    if (self::$minify && $asset->canMinify()){
                        $asset->minify();
                    }
                    $result.= str_replace('{contents}',$asset->getData(),$inlineTeplate);
                    //$result.='<script type="text/javascript">'.$asset->getData().'</script>';
                } else {
                    $assetTime = $asset->getSourceTime();
                    $assetPath = str_replace('{filename}',self::calculateCombinedName([$assetName],$assetTime),$outputPathTemplate);
                    //$assetPath = str_replace('{bundle}', self::$bundle, Config::get('assets.output.path')).'/Scripts/'.self::calculateCombinedName(array($assetName),$assetTime).'.js';
                    if (!file_exists($assetPath)) {
                        $asset->load();
                        if (self::$minify && $asset->canMinify()){
                            $asset->minify();
                        }
                        self::writeFile($assetPath,$asset->getData());
                        if (self::$gzip) {
                            self::writeFile($assetPath.'.gz',gzencode($asset->getData())); //write the compressed version
                        }
                    }
                    $result.=str_replace('{assetName}',self::unroot($assetPath),$declarationTemplate);
                    //$result.= '<script type="text/javascript" src="'.self::unroot($assetPath).'"></script>'
                }
            }
        }
        //Debug::groupClose();
        return $result;
    }

    /*
     * Given the pathname of the new combined file, it finds the old combined files and deletes them
     */
    protected static function cleanup($path) {
        $pattern = "#_[\\d]+\\.css|js$#";
        $globReq = preg_replace($pattern,'*',$path);
        //Debug::log($globReq,'glob req');
        $files = glob($globReq);
        //Debug::log($files,'old files');
        foreach ($files as $file){
            if ($file != $path && $file != $path.'.gz'){
                unlink($file);
            }
        }
    }

    protected static function writeFile($filePath, $data) {
        //Debug::log(dirname($filePath),'dirname for filepath');
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath),0644,true);
        }
        file_put_contents($filePath,$data);
    }

    protected static function calculateCombinedName($assets,$time){
        return hash("crc32b",implode('',$assets)).'_'.$time;
    }

    /**
     * remove document root from path, making it an url;
     **/
    protected static function unroot($path){
        return str_replace($_SERVER['DOCUMENT_ROOT'],'',$path);
    }

    public static function generateHeadAssets()
    {
        self::prepareOutput();
        return self::$result_head;
    }

    public static function generateBottomAssets()
    {
        self::prepareOutput();
        return self::$result_bottom;

    }
}
