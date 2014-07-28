<?php
class FabrikaAutoloader
{
    const CLASS_SUFFIX = '.php';

    private static $basePath;

    private static $initialized = false;

    private static $classMap = [
        'FirePHP' => '/Lib/FirePHPCore/FirePHP.class.php',
        'phpQuery' => '/Lib/phpQuery/phpQuery.php',
        'PasswordHash' => '/Lib/phpass/PasswordHash.php',
        'lessc' => '/Lib/LessPhp/lessc.inc.php',
        'scssc' => '/Lib/ScssPhp/scss.inc.php',
        'JSMin' => '/Lib/Minify/min/lib/JSMin.php',
        'JSMinPlus' => '/Lib/Minify/min/lib/JSMinPlus.php',
        'Minify_JS_ClosureCompiler' => '/Lib/Minify/min/lib/Minify/JS/ClosureCompiler.php',
        'Minify_CSS_Compressor'=>'/Lib/Minify/min/lib/Minify/CSS/Compressor.php'

    ];

    private static $suffixes = [
        //"Controller" => 'Controller/',
    ];

    private static $bundlePaths = [
        ''
    ];

    private static $classPaths = [
        '/Classes/',
        '/ContentClasses/',
        '/Lib/SwiftMailer/classes'
    ];



    public static function __Init(){

        if (!static::$initialized){
            static::$initialized = true;
            static::$basePath = $_SERVER['DOCUMENT_ROOT'];
        }
    }

    private static function fixPath($path){
        return str_replace('//','/',$path);
    }

    public static function autoload($className)
    {
        static::__Init();
        if (array_key_exists($className, self::$classMap)) {
            self::loadClass($className, static::$basePath . self::$classMap[$className]);
        } else {
            $classFile = self::findClass($className);
            if (!$classFile) {
                throw new \Exception('Autoload cannot find class: ' . $className);
            } else {
                self::loadClass($className, $classFile);
            }
        }
    }

    protected static function findClass($className)
    {

        $possibilities = [];

        $classParts = explode('\\',$className);


        $className = str_replace('_', '/', array_pop($classParts));

        $namespacePath = implode('/',$classParts).'/';

        $pathPrefix = '';



        //search for classes in paths
        foreach (self::$classPaths as $classPath) {
            $possibilities[] = static::$basePath . $classPath . $namespacePath . $pathPrefix. $className . self::CLASS_SUFFIX;

            foreach (self::$suffixes as $suffix => $prePath){
                if (substr($className,-strlen($suffix)) == $suffix && strlen($suffix) != strlen($className)) {
                    $pathPrefix = $prePath;
                    $possibilities[] = static::$basePath . $classPath .  $namespacePath . $pathPrefix. $className . self::CLASS_SUFFIX;
                    break;
                }
            }
        }
        foreach ($possibilities as $possibility){
            $filename = self::fixPath($possibility);        //var_dump($filename);

            if (file_exists($filename)){
                return $filename;
            }
        }

        return false;
    }


    public static function loadClass($className, $classFile)
    {
        require_once($classFile);
        if (method_exists($className, '__Init')) {
            call_user_func($className . '::__Init');
        }
    }
}

spl_autoload_register('FabrikaAutoloader::autoload');
