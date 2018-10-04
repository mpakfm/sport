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

die;
$oCore = \classes\Core::init();
$DB = \classes\Core::init()->db;
$EMPTY_PATH = __DIR__.'/upload/EMPTY.html';

$YEAR = '1999';
while ($YEAR <= 2017) {
	$PATH = __DIR__.'/upload/SWE2_'.$YEAR.'.html';
	copy($EMPTY_PATH,$PATH);
	Printu::log($PATH);
	$YEAR++;
}


/*

$sql = "SELECT g.ID, gt1.ID as ID1, gt2.ID as ID2 "
	. "FROM game g "
	. "INNER JOIN game_team gt1 ON gt1.GAME_ID = g.ID AND gt1.HOME = 1 "
	. "INNER JOIN game_team gt2 ON gt2.GAME_ID = g.ID AND gt2.HOME = 0 "
	. "WHERE gt1.SCORE = gt2.SCORE AND gt1.DRAW = 0 "
	. "LIMIT 1000";

$rs = $DB->query($sql);
$arID = [];
while ($item = $DB->get_row_assoc($rs)) {
	$arID[] = $item['ID1'];
	$arID[] = $item['ID2'];
}
Printu::log(count($arID),'count ID');
$sql = "UPDATE game_team SET WIN = 0, DRAW = 1 WHERE ID IN(". implode(',', $arID).")";
$DB->query($sql);
Printu::log($DB->affected_rows(),'updated');

*/