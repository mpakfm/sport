<?php

namespace classes\Controller;

class Analizer extends \classes\Controller {
	
	public function actionIndex() {
		$DB = \classes\Core::init()->db;
		$rs = \classes\Models\Championship::GetList();
		$arChampionships = [];
		while ($item = $DB->get_row_assoc($rs)) {
			$oCountry = \classes\Models\Country::GetById($item['COUNTRY_ID']);
			$item['COUNTRY'] = $oCountry->NAME;
			//\Mpakfm\Printu::log($item);
			$arChampionships[] = $item;
		}
		$this->setRenderTplLng('a_pair.twig');
		$this->addTplParams('Championship',$arChampionships);
		$this->addTplParams('page',['title'=>'Анализатор - Пара в чемпионате']);
	}
	
	public function actionCmd() {
		$DB = \classes\Core::init()->db;
		$rs = \classes\Models\Championship::GetList();
		$arChampionships = [];
		while ($item = $DB->get_row_assoc($rs)) {
			$oCountry = \classes\Models\Country::GetById($item['COUNTRY_ID']);
			$item['COUNTRY'] = $oCountry->NAME;
			//\Mpakfm\Printu::log($item);
			$arChampionships[] = $item;
		}
		$this->setRenderTplLng('a_cmd.twig');
		$this->addTplParams('Championship',$arChampionships);
		$this->addTplParams('page',['title'=>'Анализатор - Команда']);
	}
}

