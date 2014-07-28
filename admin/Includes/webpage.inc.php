<?php
require_once('Includes/dependencies.inc.php');
$pageStyles = array();
$pageScripts = array();

function webPageConstruct($title=''){
    require_once('Lib/phpQuery/phpQuery.php');
    $webPageTemplate=<<<WEBPAGE
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title></title>
    </head>
    <body>
        <h1>Title</h1>
        <script type="text/javascript">
            var debugEnabled = (document.location.hostname.indexOf('local') !=-1);
            function _g(logData){if (debugEnabled){if (typeof console !== 'undefined'){console.group(logData);}} return false;}
            function _u(){if (debugEnabled){if (typeof console !== 'undefined'){console.groupEnd();}}	return false;}
            function _d(){if (debugEnabled){	if (typeof console !== 'undefined'){console.log(Array.prototype.slice.call(arguments));}}	return false;}
        </script>
    </body>
</html>
WEBPAGE;



    $pPage = phpQuery::newDocument($webPageTemplate);
    $pPage->find('title')->html(conf('SITE_NAME').' - Administration - '.$title);
    $pPage->find('h1')->html($title);
    return $pPage;
}

function outputWebPage($webPage){
    _gc('Outputting Web Page');
    global $pageStyles;
    global $pageScripts;
    list($styles,$scripts) = calculateDependencies($pageStyles, $pageScripts);
    $pPage = phpQuery::newDocument($webPage);
    foreach ($styles as $style){
        if (!file_exists($_SERVER['DOCUMENT_ROOT'].conf('ADMIN_BASE_PATH').'/Css/'.$style) || $_SERVER['HTTP_HOST']== conf('HTTP_HOST_DEV')){
            require_once 'Lib/LessPhp/lessc.inc.php';
            try {
                if (file_exists(str_replace('.css','.less',$style))){
                    $lessc = new lessc();
                    $lessc->compileFile(str_replace('.css','.less',$style), $style);
                }
            } catch (exception $ex) {
                trigger_error('LESSC FATAL ERROR: '.$ex->getMessage(),E_USER_ERROR);
            }
        }
        $pPage->find('head')->append('<link href="'.$style.'" rel="stylesheet" type="text/css" media="screen"/>');
    }
    foreach ($scripts as $script){
        _d($_SERVER['DOCUMENT_ROOT'].conf('ADMIN_BASE_PATH').'/'.$script.'.php');
        if (file_exists($_SERVER['DOCUMENT_ROOT'].conf('ADMIN_BASE_PATH').'/'.$script.'.php')){
            $pPage->find('body')->append('<script src="'.$script.'.php" type="text/javascript"></script>');
        } else {
            $pPage->find('body')->append('<script src="'.$script.'" type="text/javascript"></script>');
        }
    }

    if (isset($_SESSION['login']) && ($_SESSION['login'])){
        if (isset($_COOKIE['menu_expanded']) && $_COOKIE['menu_expanded'] == '1'){
            $pPage->find('body')->addClass('expanded');
        }
    } else {
        $pPage->find('body')->addClass('full');
    }
    _u();
    return $pPage;

}

function require_script($script){
    global $pageScripts;
    if (!in_array($script, $pageScripts)){
        $pageScripts[]=$script;
    }
}

function require_style($style){
    global $pageStyles;
    if (!in_array($style, $pageStyles)){
        $pageStyles[]=$style;
    }
}

function calcDep($res){
    global $dependencies;
    $result = array($res);
    if (array_key_exists($res,$dependencies)) {
        foreach($dependencies[$res] as $depList){
            $result[]=$depList;
            $immediateDeps = calcDep($depList);
            foreach ($immediateDeps as $immediateDep){
                $result[]=$immediateDep;
            }

        }
    }
    return $result;
}

function calculateDependencies($styles,$scripts){
    _gc('calculateDependencies');
    _d($scripts,'scripts');
    _d($styles,'styles');
    global $dependencies;
    $resources = array();
    $unfilteredDeps = array();

    foreach($scripts as $script){
        $resources[]=$script;
    }
    foreach($styles as $style){
        $resources[]=$style;
    }

    foreach ($resources as $res){
        $deps = calcDep($res);
        foreach ($deps as $dep){
            $unfilteredDeps[]=$dep;
        }
    }

    $unfilteredDeps = array_reverse($unfilteredDeps);
    $filteredDeps = array_unique($unfilteredDeps);

    $outScripts=array();
    $outStyles=array();

    foreach ($filteredDeps as $dep){
        //debug(substr($dep, strlen($dep)-2));
        //debug(substr($dep, strlen($dep)-3));
        if (substr($dep, strlen($dep)-2) == 'js'){
            $outScripts[]=$dep;
            _d($dep,'Script');
        }	else if (substr($dep, strlen($dep)-3) == 'css'){
            $outStyles[]=$dep;
            _d($dep,'Style');
        }
    }
    _d($outScripts,'$outScripts');
    _d($outStyles,'$outStyles');
    _u();
    return array($outStyles,$outScripts);
}

const ROLE_DEV = 2;
const ROLE_ADMIN = 1;
const ROLE_USER = 0;

function rolePass($role,$error='') {
    if (!roleCheck($role)){
        if ($error != ''){
            setError($error,2);
        } else {
            setError('You are not authorized to perform this action',2);
        }
        redirect(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'/admin/');
    }
}

function roleCheck($role){
    return ($_SESSION['loginRole'] >= $role);
}

function role_is($role){
    return ($_SESSION['loginRole'] == $role);
}

/**
 * Redirect to new url
 * @param string $url URL to redirect to
 * @param boolean $die Die after redirecting. Default true
 */
function redirect($url,$die=true,$overridePause=false){
    if ( $_SERVER['HTTP_HOST']==conf('HTTP_HOST_DEV') && conf('DEBUG_REDIRECT')==true ){
        echo '<p>Redirecting to <a href="'.$url.'">'.$url.'</a></p>';
    } else {
        header('Location: '.$url);
    }
    if ($die == true){
        die();
    }
}
