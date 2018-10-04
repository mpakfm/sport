<?php
define('DIR',  __DIR__);
define('VENDOR',  __DIR__.'/vendor');
define('CLASSES',  __DIR__.'/classes');
define('TPL',  __DIR__.'/templates');
define('ENCRYPTION_KEY', 'ab86d144e3f080b61c7c2e43');


spl_autoload_register(function ($class) {
	$arPath = explode("\\", $class);
    include __DIR__. '/' . implode('/', $arPath) . '.php';
});


$L = require_once VENDOR.'/autoload.php';

use classes\Router;
use Mpakfm\Printu;


$oCore = \classes\Core::init();

$oCore->render();