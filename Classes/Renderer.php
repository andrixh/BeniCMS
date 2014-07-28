<?php

use Assets\Assets;

class Renderer {
    protected $env;
    protected $loader;

    public function __construct(){
        $this->loader = new Twig_Loader_Filesystem($_SERVER['DOCUMENT_ROOT'].'/ContentTemplates');
        $this->env = new Twig_Environment($this->loader, array(
            //'cache' => 'TwigCache',
        ));

        $this->env->addExtension(new Twig_Extension_StringLoader());
        //$this->env->addExtension(new Twig_Extensions_Extension_Intl());
        
        // Assets
        $assetFunction = new Twig_SimpleFunction('asset',function($assetName){
            Assets::require_asset($assetName);
        });
        $this->env->addFunction($assetFunction);

        $headAssetsFunction = new Twig_SimpleFunction('headAssets',function(){
            return Assets::generateHeadAssets();
        });
        $this->env->addFunction($headAssetsFunction);
        $bottomAssetsFunction = new Twig_SimpleFunction('bottomAssets',function(){
            return Assets::generateBottomAssets();
        });
        $this->env->addFunction($bottomAssetsFunction);

        //Component
        $componentFunction = new Twig_SimpleFunction('component',function($context, $type, $instance=null){
            _d('twig calling component '.$type,$instance);
            $componentClass = '\\Component\\'.ucfirst($type);
            $component = new $componentClass($instance,$context);
            $component->prepare();
            $result = $component->output();
            _d($result,'component result');
            return $result;
        },['is_safe' => ['html'],'needs_context' => true]);
        $this->env->addFunction($componentFunction);

        //Content
        $contentFilter = new Twig_SimpleFilter('content',function($context, $contentData, $template=''){
            $contentType = $contentData['TypeID'];
            if ($template == ''){
                $templateName = ucfirst($contentType);
            } else {
                $templateName = ucfirst($contentType).'_'.$template;
            }

            $contentData['_parent'] = $context;
            $renderer = new Renderer();
            $result = $renderer->render('/Content/'.$templateName.'.twig', $contentData);
            return $result;
        },['is_safe'=>['html'],'needs_context' => true]);
        $this->env->addFilter($contentFilter);

        //Image
        $imageFilter = new Twig_SimpleFilter('image',function($imgData,$options=[]){
            $img = new \Decoder\Image($imgData,$options);
            return $img->decode();
        },['is_safe' => ['html']]);
        $this->env->addFilter($imageFilter);

        //Image test
        $imageTest = new Twig_SimpleTest('image',function($var){
            return $var instanceof Resource\Image;
        });
        $this->env->addTest($imageTest);

        //Video
        $videoFilter = new Twig_SimpleFilter('video',function($videoData,$options=[]){
            _d($videoData,'Twig video filter');
            _d($options,'Twig video filter');
            $vid = new \Decoder\Video($videoData,$options);
            return $vid->decode();
        },['is_safe' => ['html']]);
        $this->env->addFilter($videoFilter);

        //Video Test
        $imageTest = new Twig_SimpleTest('video',function($var){
            return $var instanceof Resource\Video;
        });
        $this->env->addTest($imageTest);

        //Video Thumbnail
        $videoThumb = new Twig_SimpleFilter('videoThumb',function($var){
            return $var->getThumbnail();
        });
        $this->env->addFilter($videoThumb);

        $fileDownload = new Twig_SimpleFilter('fileurl',function(\Resource\File $file){
            return '/getFile/'.$file->identifier;
        },['is_safe' => ['html']]);
        $this->env->addFilter($fileDownload);

        // Inteface String (to be deprecated)
        $interface = new Twig_SimpleFunction('interface',function($interfaceID){
            $result = '';
            _d($interfaceID,'interfaceID');
            $strID = DB::row(Query::Select('interface')->eq('strID',$interfaceID)->limit(1));
            _d($strID,'strID');
            if ($strID) {
                $dsc = new DSCache();
                $dsc->add($strID->value);
                $dsc->load();
                $result = $dsc->get($strID->value);
                _d($result,'result');
            }
            return $result;
        },['is_safe' => ['html']]);
        $this->env->addFunction($interface);

    }

    public function render($template,$data){

        $TplData =[
            'Site'=>Site::getInstance()
        ];

        $TplData = array_merge($TplData,$data);
        _d($TplData,'rendering twig');
        return $this->env->render($template,$TplData);
    }

}