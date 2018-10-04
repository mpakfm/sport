<?php

namespace classes\Lib;

class Parser {
	
	static $id = 1;
	
	public $url;
	public $arResult;
	public $siteCheck;
	
	public function __construct($url = '') {
		$this->url = $url;
		//$this->siteCheck = $this->isDomainAvailible($this->url);
		
	}
	
	public function grabber() {
		//Проверка на правильность URL 
		if(!filter_var($this->url, FILTER_VALIDATE_URL)) {
				return false;
		}
		//Инициализация curl
		$curlInit = curl_init($this->url);
		curl_setopt ($curlInit, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($curlInit,CURLOPT_HEADER,false);
		//curl_setopt($curlInit,CURLOPT_NOBODY,true);
		curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

		//Получаем ответ
		$response = curl_exec($curlInit);

		curl_close($curlInit);
		
		return $response;
	}
	
	public function grabber_game($data,$champ_id) {
		//https://www.championat.com/football/_england/2214/calendar.html
		$this->url = 'https://www.championat.com/football/_'.$data['PARSER_URL'].'/'.$champ_id.'/calendar.html';
		\Mpakfm\Printu::log($this->url,'grabber_game $this->url','file');
		$html = $this->grabber();
		
		return $this->game_parser($html, $data);
	}
	
	public function grabber_cmd($data,$champ_id) {
		//$url = 'https://www.championat.com/football/_england/2214/table/all.html';
		$this->url = 'https://www.championat.com/football/_'.$data['PARSER_URL'].'/'.$champ_id.'/table/all.html';
		\Mpakfm\Printu::log($this->url,'grabber_cmd $this->url','file');
		$html = $this->grabber();
		$resTableAll = $this->cmd_parser($html, $data);
		if ($resTableAll['all']!= 0 )
			return $this->cmd_parser($html, $data);
		else {
			$this->url = 'https://www.championat.com/football/_'.$data['PARSER_URL'].'/'.$champ_id.'/table/all/group.html';
			\Mpakfm\Printu::log($this->url,'grabber_cmd $this->url','file');
			$html = $this->grabber();
			$resTableAll = $this->cmd_parser($html, $data);
			return $this->cmd_parser($html, $data);
		}
		//group.html
	}
	
	public function grabber_seasons($data) {
		\Mpakfm\Printu::log($data,'grabber_seasons $data','file');
		//$url = 'https://www.championat.com/football/_england.html';
		$this->url = 'https://www.championat.com/football/_'.$data['PARSER_URL'].'.html';
		\Mpakfm\Printu::log($this->url,'$this->url','file');
		$html = $this->grabber();
		//\Mpakfm\Printu::log(strlen($html),'strlen($html)','file');
		return $this->seasons_parser($html, $data);
	}
	
	public function isDomainAvailible($domain) {
		//Проверка на правильность URL 
		if(!filter_var($domain, FILTER_VALIDATE_URL)) {
				return false;
		}

		//Инициализация curl
		$curlInit = curl_init($domain);
		curl_setopt ($curlInit, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($curlInit,CURLOPT_HEADER,true);
		curl_setopt($curlInit,CURLOPT_NOBODY,true);
		curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

		//Получаем ответ
		$response = curl_exec($curlInit);

		curl_close($curlInit);

		if ($response) return true;

		return false;
	}
	
	public function analizer($html) {	
		$regexp = '|<table class="table b-table-sortlist">(.*)<tbody>(.*)</tbody>(.*)</table>|Uis';
		$arMatches = [];
		preg_match($regexp, $html, $arMatches);
		$tbody = $arMatches[2];
		
		$regexp = '|<tr>(.*)</tr>|Uis';
		$arMatches = [];
		preg_match_all($regexp, $tbody, $arMatches);
		
		$lines = $arMatches[1];
		
		$iChampId = '';
		
		$iMatchId = 0;
		$sMatchTime = '';
		$iTeamHomeId = '';
		$iTeamHomeName = '';
		$iTeamHomeScore = '';
		$iTeamGuestId = '';
		$iTeamGuestScore = '';
		
		return $this->arResult;
	}
	
	public function game_parser($html, $data) {
		\Mpakfm\Printu::log(strlen($html),'strlen $html','file');
		\Mpakfm\Printu::log($html,'game_parser $html','file','game_parser.log');
		$arResult = [
			'all'=>0,
			'games'=>[],
			'cnt'=>0
		];
		/*
		$regexp = '|<div class="sport__table sport__calendar__table">(.*)</table>|Uis';
		$regexp = '|<table class="table b-table-sortlist">(.*)</table>|Uis';
		$regexp = '|<table class="table b-table-sortlist">(.*)</table>|Uis';
		$arMatches = [];
		preg_match($regexp, $html, $arMatches);
		\Mpakfm\Printu::log($arMatches,'games $arMatches','file');
		if (empty($arMatches)) {
			\Mpakfm\Printu::log($regexp,'games $regexp','file');
			\Mpakfm\Printu::log('empty','games table','file');
			
			$regexp = '|<tr(.*)>(.*)</tr>|Uis';
		$arMatches = [];
		preg_match_all($regexp, $html, $arMatches);
		\Mpakfm\Printu::log(count($arMatches),'count TR $arMatches','file');
		\Mpakfm\Printu::log(count($arMatches[2]),'count $arMatches[2]','file');
			
			return $arResult;
		}
		 * 
		 */
		//$tbody = $arMatches[1];
		$regexp = '|<tr(.*)>(.*)</tr>|Uis';
		$arMatches = [];
		preg_match_all($regexp, $html, $arMatches);
		\Mpakfm\Printu::log(count($arMatches),'count TR $arMatches','file');
		if (empty($arMatches)) return $arResult;
		\Mpakfm\Printu::log(count($arMatches[2]),'count $arMatches[2]','file');
		unset($arMatches[2][0]);

		foreach ($arMatches[2] as $line) {
			$arResult['all']++;
			/*
			// tour
			// <td class="sport__calendar__table__tour" sortOrder="1" data-value="1">1</td>
			$regexpTour = '|<td class="sport__calendar__table__tour"(.*)>(\d+)</td>|Uis';
			$arTour = [];
			preg_match_all($regexpTour, $line, $arTour);
			$iTour = (int)$arTour[2];
			\Mpakfm\Printu::log($iTour,'$iTour','file');
			 * 
			 */
			// date
			// <td class="sport__calendar__table__date" data-value="2018_08"> 03.08.2018, 22:00 </td>
			$regexpDate = '|<td class="sport__calendar__table__date"(.*)>\s+(.*)\s+</td>|Uis';
			$arDate = [];
			preg_match_all($regexpDate, $line, $arDate);
			if (!isset($arDate[2][0])) {
				//\Mpakfm\Printu::log($line,'EMPTY DATE $line','file');
				continue;
			}
			$dt = date_create_from_format("d.m.Y, H:i", trim($arDate[2][0]));
			//\Mpakfm\Printu::log($dt,'DATE','file');
			if (!$dt) continue;
			// teams - 2 штуки
			//<a href="/football/_england/2615/team/100997/result.html" class="sport__calendar__table__team" data-value="100997">Рединг</a>
			$regexpTeam = '|<a href="(.*)team/(\d+)/result.html"(.*)>(.*)</a>|Uis';
			$arTeam = [];
			preg_match_all($regexpTeam, $line, $arTeam); // 2
			//\Mpakfm\Printu::log($arTeam[2],'$arTeam','file');
			if (empty($arTeam) || empty($arTeam[2])) {
				//\Mpakfm\Printu::log($line,'EMPTY TEAMS $line','file');
				continue;
			}
			// game
			// <a href="/football/_england/2615/match/689583.html">
			$regexpGame = '|<a href="(.*)match/(\d+)\.html"|Uis';
			$arGame = [];
			preg_match($regexpGame, $line, $arGame); // 2
			//\Mpakfm\Printu::log($arGame[2],'$arGame[2]','file');
			if (empty($arGame) || !$arGame[2]) {
				//\Mpakfm\Printu::log($line,'EMPTY GAME $line','file');
				continue;
			}
			// result
			// <span class="sport__calendar__table__result__left">1</span>
			// <span class="sport__calendar__table__result__right">2</span>
			$regexpScores = '|<span class="sport__calendar__table__result__(.*)>(\d+)</span>|Uis';
			$arScores = [];
			preg_match_all($regexpScores, $line, $arScores); // 2
			//\Mpakfm\Printu::log($arScores[2],'$arScores','file');
			if (empty($arScores) || empty($arScores[2])) {
				//\Mpakfm\Printu::log($line,'EMPTY SCORES $line','file');
				continue;
			}
			if ($arScores[2][0] > $arScores[2][1]) {
				$bHomeWin = true; $bDrow = false;
			} elseif ($arScores[2][0] < $arScores[2][1]) {
				$bHomeWin = false; $bDrow = false;
			} else {
				 $bDrow = true; $bHomeWin = false;
			}
			$aGame = \classes\Models\Game::GetByCodeParser($arGame[2], $data['PARSER_ID']);
			//\Mpakfm\Printu::log($aGame,'aGame','file');
			if (!$aGame) {
				//\Mpakfm\Printu::log(false,'if aGame','file');
				$arLeftCmd = \classes\Models\Team::GetByCode($arTeam[2][0], $data['PARSER_ID'], $data['CHY_ID']);
				$arRightCmd = \classes\Models\Team::GetByCode($arTeam[2][1], $data['PARSER_ID'], $data['CHY_ID']);
				$oGame = new \classes\Models\Game();
				$oGame->NAME = $arLeftCmd['NAME'].' - '.$arRightCmd['NAME'];
				$oGame->CHY_ID = $data['CHY_ID'];
				$oGame->GAME_DATE = $dt->format('Y-m-d');
				$oGame->Insert();
				\classes\Models\Game::UpdateParserCode($oGame->ID, $data['PARSER_ID'], $arGame[2]);
				$oGT = new \classes\Models\GameTeam();
				$oGT->GAME_ID = $oGame->ID;
				$oGT->TEAM_ID = $arLeftCmd['ID'];
				$oGT->HOME = 1;
				$oGT->WIN = ($bHomeWin===true?1:0);
				$oGT->DRAW = ($bDrow===true?1:0);
				$oGT->SCORE = $arScores[2][0];
				$oGT->Insert();
				$oGT = new \classes\Models\GameTeam();
				$oGT->GAME_ID = $oGame->ID;
				$oGT->TEAM_ID = $arRightCmd['ID'];
				$oGT->HOME = 0;
				$oGT->WIN = ($bHomeWin===false?1:0);
				$oGT->DRAW = ($bDrow===true?1:0);
				$oGT->SCORE = $arScores[2][1];
				$oGT->Insert();
				$arResult['cnt']++;
				$arResult['games'][] = [
					'ID'=>$oGame->ID,
					'NAME'=>$oGame->NAME,
					'GAME_DATE'=>$dt->format('d.m.Y'),
					'SCORES'=>$arScores[2][0].':'.$arScores[2][1]
				];
			} else {
				$dt = date_create_from_format('Y-m-d', $aGame['GAME_DATE']);
				$arResult['games'][] = [
					'ID'=>$aGame['ID'],
					'NAME'=>$aGame['NAME'],
					'GAME_DATE'=>$dt->format('d.m.Y'),
					'SCORES'=>$arScores[2][0].':'.$arScores[2][1]
				];
			}
		}
		return $arResult;
	}
	
	public function cmd_parser($html, $data) {
		
		$arResult = [
			'all'=>0,
			'code'=>[],
			'cnt'=>0
		];
		$regexp = '|<div class="page__block sport">(.*)</table>|Uis';
		$arMatches = [];
		preg_match($regexp, $html, $arMatches);
		if (empty($arMatches)) return $arResult;
		$tbody = $arMatches[1];
		$regexp = '|<tr(.*)>(.*)</tr>|Uis';
		$arMatches = [];
		preg_match_all($regexp, $tbody, $arMatches);
		$lines = $arMatches[2];
		// NAME
		//<td ... ><a href="/football/_england/2214/team/63890/result.html">Манчестер Сити</a></td>
		$regexp = '|<td(.*)><a(.*)team/(\d+)/result.html">(.*)</a>|Uis';
		$arMatches = [];
		preg_match_all($regexp, $tbody, $arMatches);

		$DB = \classes\Core::init()->db;
		
		$rsChampTeam = \classes\Models\ChampionshipTeam::GetList(['CHY_ID'=>['table'=>'cht','type'=>'=','value'=>$data['CHY_ID']]]);
		$arChampTeam = [];
		while ($item = $DB->get_row_assoc($rsChampTeam)) {
			$arChampTeam[$item['TP_CODE']] = $item;
		}

		$arCodeTeams = [];
		$arResult['all'] = 0;
		foreach ($arMatches[4] as $key=>$name) {
			$arResult['all']++;
			if (isset($arChampTeam[$arMatches[3][$key]])) {
				$oTeam = \classes\Models\Team::GetById($arChampTeam[$arMatches[3][$key]]['TEAM_ID']);
				$arTeams = \classes\Models\Team::GetByAliases($oTeam->NAME, $arMatches[3][$key], $data);
			} else {
				$arTeams = \classes\Models\Team::GetByAliases($name, $arMatches[3][$key], $data);
			}
			$arCodeTeams[$arMatches[3][$key]] = [
				'PARSER_NAME' =>$name,
				'VARIANTS' => $arTeams
			];
		}
		return ['all'=>$arChampTeam,'code'=>$arCodeTeams,'cnt'=>$arResult['all']];
	}
	
	public function seasons_parser($html, $data) {
		$arResult = [
			'all'=>0,
			'insert'=>0,
			'update'=>0
		];
		$arChamionship = [];
		$regexp = '|<select class="js-sport-head-tournir" data-year="(.*)"(.*)>(.*)</select>|Uis';
		$regexp = '|<select data-year="(.*)"(.*)>(.*)</select>|Uis';

		$arMatches = [];
		preg_match_all($regexp, $html, $arMatches);
		//\Mpakfm\Printu::log($arMatches,'$arMatches','file');
		if (!isset($arMatches[1]) || empty($arMatches[1])) return $arResult;
		$DB = \classes\Core::init()->db;
		\Mpakfm\Printu::log($data['PARSER_URL'],'PARSER_URL','file');
		$start_year = 0;
		if ($data['PARSER_URL'] == 'other') $start_year = 1;
		\Mpakfm\Printu::log($start_year,'$start_year','file');
		\Mpakfm\Printu::log($arMatches[1],'$arMatches','file');
		
		foreach ($arMatches[1] as $key => $year) {
			$arMatches2 = [];
			$iRealYear = $year - $start_year;
			$regexp = '|<option value="/football/(.*)html" data-id="(\d+)">(.+)</option>|Uis';
			// <option value="2627" data-href="/football/_spain/2627/table/all.html">Сегунда</option>
			$regexp = '|<option value="(\d+)" data-href="/football/(.*)html">(.+)</option>|Uis';
			preg_match_all($regexp, $arMatches[3][$key], $arMatches2);

			foreach ($arMatches2[3] as $kkey => $ch_name) {
				
				if ($ch_name != $data['OUTER_CODE']) continue;
				$rsChampionShip = \classes\Models\Championship::GetList([
					'COUNTRY_ID'=>['table'=>'ch','type'=>'=','value'=>$data['COUNTRY_ID']],
					'PARSER_ID'=>['table'=>'ch','type'=>'=','value'=>self::$id],
					'OUTER_CODE'=>['table'=>'ch','type'=>'LIKE','value'=>$ch_name]
				]);
				$arChampionShip = $DB->get_row_assoc($rsChampionShip);
				if (!$arChampionShip) continue;
				$arResult['all']++;
				$rsChYear = \classes\Models\ChampionshipYear::GetList([
					'CH_ID'=>['table'=>'chy','type'=>'=','value'=>$arChampionShip['ID']],
					'YEAR'=>['table'=>'chy','type'=>'=','value'=>$iRealYear]
				]);
				$arChYear = $DB->get_row_assoc($rsChYear);
				// Insert new season
				if (!$arChYear) {
					$oChYear = new \classes\Models\ChampionshipYear();
					$oChYear->CH_ID = $arChampionShip['ID'];
					$oChYear->YEAR = $iRealYear;
					$oChYear->START = \classes\Models\ChampionshipYear::$arCountryStart[$arChampionShip['COUNTRY_ID']];
					$oChYear->ROUNDS = 2;
					$oChYear->Save();
					$oChYear->SaveParseCode($arMatches2[1][$kkey], self::$id);
					$arResult['insert']++;
					\Mpakfm\Printu::log($oChYear,'$oChYear','file');
				}
				/*
				// Update parse code
				elseif (!$arChYear['P_CODE']) {
					$oChYear = \classes\Models\ChampionshipYear::GetById($arChYear['ID']);
					$oChYear->SaveParseCode($arMatches2[2][$kkey], self::$id);
					$arResult['update']++;
				}
				 * 
				 */
			}
		}
		return $arResult;
	}

}
