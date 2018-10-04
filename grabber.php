<?php

/*
SELECT g.ID, gt1.SCORE, gt1.WIN, gt1.DRAW, gt2.SCORE, gt2.WIN, gt2.DRAW, gt1.ID, gt2.ID FROM game g inner JOIN game_team gt1 ON gt1.GAME_ID = g.ID AND gt1.HOME = 1 inner JOIN game_team gt2 ON gt2.GAME_ID = g.ID AND gt2.HOME = 0 WHERE gt1.SCORE = gt2.SCORE AND gt1.DRAW = 0 
 */

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

Printu::log('GRABBER');

$path = 'https://www.championat.com/football/_worldcup/1589/calendar/group.html';
$file = file_get_contents($path);
//Printu::log($file);

// Parcing
preg_match_all('|<td(.*)sport__calendar__table__label(.*)sport__calendar__table__report|Uis', $file, $matches);

Printu::log(count($matches[0]),'lines $matches[0]');
Printu::log($matches[0],'$matches[0]');
Printu::log($matches,'$matches');