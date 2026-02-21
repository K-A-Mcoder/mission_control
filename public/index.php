<?php

define('BASE_PATH',    dirname(__DIR__));
define('APP_PATH',     BASE_PATH . '/app');
define('CONFIG_PATH',  APP_PATH  . '/config');
define('VIEWS_PATH',   APP_PATH  . '/views');
define('STORAGE_PATH', BASE_PATH . '/storage');

require BASE_PATH . '/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$kernel  = require BASE_PATH . '/bootstrap/app.php';
$request = Etus\Framework\Http\Request::capture();

$kernel->handle($request)->send();
