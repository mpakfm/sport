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

die;
$oCore = \classes\Core::init();
$DB = \classes\Core::init()->db;
$iGames = 0;
$COUNTRY_ID = '14';
$CH_ID = '27';
$FILE_NAME = 'BEL_';
$YEAR1 = '2017';
$YEAR2 = '1998';
$arTeams = [];
$lines = [];
set_time_limit(0);
for ($YEAR = $YEAR1; $YEAR >= $YEAR2; $YEAR--) {

// Get ChampionshipYear
$rsCHY = classes\Models\ChampionshipYear::GetList(['CH_ID'=>['table'=>'chy','type'=>'=','value'=>$CH_ID],'YEAR'=>['table'=>'chy','type'=>'=','value'=>$YEAR]]);

if ($DB->num_rows($rsCHY)) {
	$arCHY = $DB->get_row_assoc($rsCHY);
	$oCHY = classes\Models\ChampionshipYear::GetById($arCHY['ID']);
} else {
	$oCHY = new classes\Models\ChampionshipYear([
		'CH_ID'=>$CH_ID,
		'YEAR'=>$YEAR,
		'START'=>'spring', // spring autumn
		'ROUNDS'=>'2',
	]);
	//$oCHY->Save();
	Printu::log($oCHY,'Not Save $oCHY');
}
Printu::log($oCHY,'ChampionshipYear');

// FILE
Printu::log('upload/'.$FILE_NAME.$YEAR.'.html','FILE');
$file = file_get_contents('upload/'.$FILE_NAME.$YEAR.'.html');
if (!strlen($file)) {
	//die('FILE NOT FOUND');
	Printu::log('upload/'.$FILE_NAME.$YEAR.'.html','FILE NOT FOUND');
	continue;
}

// Parcing
preg_match_all('|<tr(.*)(stage-finished)(.*)>(.*)</tr>|Uis', $file, $matches);
$lines = array_reverse($matches[4]);
unset($matches);
//Printu::log($lines,'$lines');
$last_month = false;
$DT_YEAR = $YEAR;
foreach ($lines as $line) {
	preg_match('|<td(.*)(time)(.*)>(\d\d\.\d\d)\.\s(.*)</td>|Uis', $line, $matches);
	if (isset($matches[4])) {
		$dt = explode('.', $matches[4]);
		// Смена года
		if ($last_month && $dt[1] < $last_month) $DT_YEAR++;
		
		$game_date = $DT_YEAR.'-'.$dt[1].'-'.$dt[0];
		$last_month = $dt[1];
		//Printu::log($dt,$game_date);
	} else continue;
	unset($matches);
	preg_match('|<span class="padr">(.*)<(.*)</span>|Uis', $line, $matches);
	$H_NAME = trim(strip_tags($matches[1]));
	preg_match('|<span class="padl">(.*)<(.*)</span>|Uis', $line, $matches);
	$G_NAME = trim(strip_tags($matches[1]));
	//Printu::log($H_NAME.' - '.$G_NAME,'GAME');
	preg_match('|<td class="(.*)score(.*)>(.*)</td>|Uis', $line, $matches);
	//Printu::log($matches[3],'$matches[3]');
	$matches[3] = str_replace('&nbsp;', '', $matches[3]);
	//Printu::log($matches[3],'$matches[3]');
	$arScores = explode(':',$matches[3]);
	if (!isset($arScores[0])) $arScores[0] = 0;
	if (!isset($arScores[1])) $arScores[1] = 0;
	$H_SCORE = (int)trim($arScores[0]);
	$G_SCORE = (int)trim($arScores[1]);
	//Printu::log($arScores,'$arScores');
	
	if (!isset($arTeams[$H_NAME])) {
		$oHTeam = \classes\Models\Team::GetByAlias($H_NAME,$COUNTRY_ID);
		if (!$oHTeam) {
			$oHTeam = new \classes\Models\Team([
				'COUNTRY_ID'=>$COUNTRY_ID,
				'NAME'=>$H_NAME,
			]);
			$oHTeam->Save();
			//Printu::log($oHTeam->NAME,$oHTeam->ID.' new team');
		}
		$arTeams[$H_NAME] = $oHTeam->ID;
	}
	if (!isset($arTeams[$G_NAME])) {
		$oGTeam = \classes\Models\Team::GetByAlias($G_NAME,$COUNTRY_ID);
		if (!$oGTeam) {
			$oGTeam = new \classes\Models\Team([
				'COUNTRY_ID'=>$COUNTRY_ID,
				'NAME'=>$G_NAME,
			]);
			$oGTeam->Save();
			//Printu::log($oGTeam->NAME,$oGTeam->ID.' new team');
		}
		$arTeams[$G_NAME] = $oGTeam->ID;
	}
	$oGame = new classes\Models\Game([
		'NAME'=>$H_NAME.' - '.$G_NAME,
		'CHY_ID'=>$oCHY->ID,
		'GAME_DATE'=>$game_date
	]);
	$oGame->Save();
	$arCmd = [];
	$arCmd[0] = [
		'GAME_ID'=>$oGame->ID,
		'TEAM_ID'=>$arTeams[$H_NAME],
		'HOME'=>1,
		'WIN'=>0,
		'DRAW'=>0,
		'SCORE'=>$H_SCORE
	];
	$arCmd[1] = [
		'GAME_ID'=>$oGame->ID,
		'TEAM_ID'=>$arTeams[$G_NAME],
		'HOME'=>0,
		'WIN'=>0,
		'DRAW'=>0,
		'SCORE'=>$G_SCORE
	];
	if ($arCmd[0]['SCORE'] > $arCmd[1]['SCORE']) {
		$arCmd[0]['WIN'] = 1;
	}
	elseif ($arCmd[0]['SCORE'] < $arCmd[1]['SCORE']) {
		$arCmd[1]['WIN'] = 1;
	}
	else {
		$arCmd[0]['DRAW'] = 1;
		$arCmd[1]['DRAW'] = 1;
	}
	$oHome = new classes\Models\GameTeam($arCmd[0]);
	$oGuest = new classes\Models\GameTeam($arCmd[1]);
	$oHome->Save();
	$oGuest->Save();
	$iGames++;
	//Printu::log($arTeams[$H_NAME],$H_NAME);
	//Printu::log($arTeams[$G_NAME],$G_NAME);
}

$rsCHT = classes\Models\ChampionshipTeam::GetList(['CHY_ID'=>['table'=>'cht','type'=>'=','value'=>$oCHY->ID]]);
Printu::log($DB->num_rows($rsCHT),'num_rows ChampionshipTeam');

if (!$DB->num_rows($rsCHT)) {
	$iChampionshipTeam = 0;
	$arTeamIDs = array_flip($arTeams);
	natcasesort($arTeamIDs);
	$arSortKeys = array_keys($arTeamIDs);
	foreach ($arSortKeys as $number=>$id) {
		$oCHT = new classes\Models\ChampionshipTeam([
			'CHY_ID'=>$oCHY->ID,
			'TEAM_ID'=>$id,
			'NUMBER'=>($number+1),
		]);
		$oCHT->Save();
		$iChampionshipTeam++;
	} 
	Printu::log($iChampionshipTeam,'added ChampionshipTeam');
}
Printu::log($iGames,'add Games');

}

