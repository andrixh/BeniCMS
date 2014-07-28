<?php
header('Content-Type:text/html; charset=UTF-8');
session_name(md5($_SERVER['SERVER_NAME'].'site'));
session_start();
require_once('autoloader.php');

if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/';
}

require_once('debug.inc.php');

if (Languages::$count == 0) {
    die('no languages set');
}

$request = Request::getInstance();

_d($request,'request');

$currentPage = Pages::getCurrent();
$currentPage->prepare();
$currentPage->output();
