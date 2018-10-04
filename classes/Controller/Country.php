<?php

namespace classes\Controller;

class Country extends \classes\Controller {
	
	public function actionIndex() {
		
		$this->addTplParams('page',['title'=>'Страны - Чемпионаты']);
		
		if (!isset(\classes\Core::init()->router->PATH[2])) {
			\classes\Core::init()->router->Redirect('/');
			return;
		}
		
		$CODE = \classes\Core::init()->router->PATH[2];
		if (isset(\classes\Core::init()->router->PATH[3]) && (int)\classes\Core::init()->router->PATH[3]) {
			return $this->pageChampionship();
		}
		$this->setRenderTplLng('country.twig');
		$rsList = \classes\Models\Championship::GetList(['CODE'=>['table'=>'c','type'=>'=','value'=>$CODE]]);
		$DB = \classes\Core::init()->db;
		$arResult = [];
		while ($item = $DB->get_row_assoc($rsList)) {
			$arResult[] = $item;
		}
		$this->addTplParams('ChampionshipYear',$arResult);
	}
	
	public function pageChampionship() {
		$this->setRenderTplLng('championships.twig');
		$COUNTRY_CODE = \classes\Core::init()->router->PATH[2];
		$CHAMP_ID = \classes\Core::init()->router->PATH[3];
		$rsList = \classes\Models\ChampionshipYear::GetList(['CODE'=>['table'=>'c','type'=>'=','value'=>$COUNTRY_CODE],'ID'=>['table'=>'ch','type'=>'=','value'=>$CHAMP_ID]],['field'=>'YEAR','desc'=>true]);
		$DB = \classes\Core::init()->db;
		$arResult = [];
		while ($item = $DB->get_row_assoc($rsList)) {
			if ($item['START'] == 'autumn') {
				$end = $item['YEAR']+1;
			} else {
				$end = $item['YEAR'];
			}if ($end > $item['YEAR'])
			$item['YEAR'] .= '/' . $end;
			$item['TEAMS'] = \classes\Models\ChampionshipTeam::GetListCnt(['CHY_ID'=>['table'=>'cht','type'=>'=','value'=>$item['ID']]]);
			$item['GAMES'] = \classes\Models\Game::GetListCnt(['CHY_ID'=>['table'=>'g','type'=>'=','value'=>$item['ID']]]);
			$arResult[] = $item;
		}
		$rsTeams = \classes\Models\Team::GetList(['CODE'=>['table'=>'c','type'=>'=','value'=>$COUNTRY_CODE]],['field'=>'NAME','desc'=>false]);
		$arCountryTeams = [];
		while ($item = $DB->get_row_assoc($rsTeams)) {
			$arCountryTeams[] = $item;
		}
		$this->addTplParams('ChampionshipYear',$arResult);
		$this->addTplParams('CountryTeams',$arCountryTeams);
	}

	public function actionTeams() {
		$this->setRenderTplLng('country_teams.twig');
		$this->addTplParams('page',['title'=>'Страны - Команды']);
		
		if (!isset(\classes\Core::init()->router->PATH[2])) {
			\classes\Core::init()->router->Redirect('/');
			return;
		}
		$CODE = \classes\Core::init()->router->PATH[2];
		
		$rsList = \classes\Models\Team::GetList(['CODE'=>['table'=>'c','type'=>'=','value'=>$CODE]],['field'=>'NAME','desc'=>false]);
		
		$DB = \classes\Core::init()->db;
		$arResult = [];
		while ($item = $DB->get_row_assoc($rsList)) {
			$item['GAMES'] = \classes\Models\Team::GetCntGames($item['ID']);
			$arResult[] = $item;
		}
		$this->addTplParams('Teams',$arResult);
	}
}

