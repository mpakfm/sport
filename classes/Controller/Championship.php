<?php

namespace classes\Controller;

class Championship extends \classes\Controller {
	
	public function actionIndex() {
		
		$this->setRenderTplLng('championship.twig');
		$this->addTplParams('page',['title'=>'Чемпионаты']);
		
		$all_pages = [];
		//$all_pages = \classes\Core::init()->arPages;
		$arPages = [];
		foreach ($all_pages as $item) {
			$page = [
				'code'=>$item['code'],
				'url'=>$item['url'],
				'menu'=>$item['menu'],
				'static'=>$item['static'],
				'lang'=>$item[\classes\Core::init()->i18n->locale],
				'tpl'=>\classes\Core::init()->i18n->locale.'/'.$item['code'].'.twig'
			];
			$model = 'classes\\Models\\'.ucfirst($item['code']);
			if (\classes\Core::checkClass($model)) {
				$oModel = new $model();
				//\Mpakfm\Printu::log($oModel->data,'data');
				$page['data'] = $oModel->data_lng;
			}
			$arPages[] = $page;
		}
		$this->addTplParams('all_pages',$arPages);
		//\Mpakfm\Printu::log(\classes\Core::init()->router,'router');
		$ID = (int)\classes\Core::init()->router->PATH[1];
		if (!$ID) {
			\classes\Core::init()->router->Redirect('/');
			return;
		}
		
		$oChampYear = \classes\Models\ChampionshipYear::GetById($ID);
		$oChamp = \classes\Models\Championship::GetById($oChampYear->CH_ID);
		$oCountry = \classes\Models\Country::GetById($oChamp->COUNTRY_ID);
		
		if ($oChampYear->START == 'autumn') {
			$end = $oChampYear->YEAR + 1;
		} else {
			$end = $oChampYear->YEAR;
		}
		$YEAR = $oChampYear->YEAR;
		if ($end > $oChampYear->YEAR)
			$YEAR .=  '/' . $end;
		$caption = $oCountry->NAME . '. ' . $oChamp->NAME . ' (' . $YEAR . ')';
		$this->addTplParams('caption',$caption);
		
		$rsList = \classes\Models\Game::GetList(['CHY_ID'=>['table'=>'g','type'=>'=','value'=>$oChampYear->ID]]);
		$DB = \classes\Core::init()->db;
		$arResult = [];
		while ($item = $DB->get_row_assoc($rsList)) {
			
			$dt = date_create_from_format('Y-m-d',$item['GAME_DATE']);
			$arResult[] = [
				'ID'=>$item['ID'],
				'GAME'=>$item['H_CMD'] . ' - ' . $item['G_CMD'],
				'SCORES'=>$item['H_SCORE'] . ' : ' . $item['G_SCORE'],
				'DATE'=>$dt->format('d.m.Y')
			];
		}
		$this->addTplParams('ChampionshipYear',$arResult);
		
		/*
		$rsList = \classes\Models\ChampionshipYear::GetList();
		$DB = \classes\Core::init()->db;
		$arResult = [];
		while ($item = $DB->get_row_assoc($rsList)) {
			if ($item['START'] == 'autumn') {
				$end = ceil($item['ROUNDS']*0.5 + $item['YEAR']);
			} else {
				$end = ceil($item['ROUNDS']*0.5 + $item['YEAR'])-1;
			}if ($end > $item['YEAR'])
			$item['YEAR'] .= '/' . $end;
			$arResult[] = $item;
		}
		$this->addTplParams('ChampionshipYear',$arResult);
		
		 * 
		 */
		//INSERT INTO `game` (`ID`, `NAME`, `CHY_ID`, `GAME_DATE`) VALUES (NULL, NULL, '2', '2017-08-19');
		
		//INSERT INTO `game_team` (`ID`, `GAME_ID`, `TEAM_ID`, `HOME`, `WIN`, `DRAW`, `SCORE`) VALUES (NULL, '11', '21', '1', '1', '0', '1'), (NULL, '11', '1', '0', '0', '0', '0');
	}
}

