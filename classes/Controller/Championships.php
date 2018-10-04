<?php

namespace classes\Controller;

class Championships extends \classes\Controller {
	
	public function actionIndex() {
		
		$this->setRenderTplLng('championships.twig');
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
		
		
		$rsList = \classes\Models\ChampionshipYear::GetList([],['field'=>'YEAR','desc'=>true]);
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
		$this->addTplParams('ChampionshipYear',$arResult);
		
	}
}

