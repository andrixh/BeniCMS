<?php
session_name(md5($_SERVER['SERVER_NAME'].'admin'));
session_start();
if (!isset($_SESSION['login'])){
    header('Location: login.php');
    die();
} else {
    //clear cache
    require_once('Config/config.php');


    $files = array_merge(glob(conf('path.cache.structure').'*'),glob(conf('path.cache.html').'*'));
    foreach($files as $file){ // iterate files
        if (is_file($file)) {
            unlink($file); // delete file
        }
    }
    //
}