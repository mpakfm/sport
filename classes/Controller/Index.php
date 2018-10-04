<?php

namespace classes\Controller;

class Index extends \classes\Controller {
	
	public function actionIndex() {
		
		$this->setRenderTplLng('index.twig');
		$this->addTplParams('page',['title'=>'Главная']);
		
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
		/*
		$oUser = \classes\Models\User::GetById(1);
		\Mpakfm\Printu::log($oUser,'Get admin');
		\Mpakfm\Printu::log($oUser->PWD_HASH,'$oUser->PWD_HASH');
		$check = \classes\Auth::verifyPwd(4505, $oUser->PWD_HASH);
		\Mpakfm\Printu::log($check,'verifyPwd 4505 $oUser->PWD_HASH');
		$oUser->UpdatePwd(4599);
		\Mpakfm\Printu::log($oUser->PWD_HASH,'set new PWD_HASH');
		$oUser = \classes\Models\User::GetById(1);
		\Mpakfm\Printu::log($oUser->PWD_HASH,'Get admin $oUser->PWD_HASH');
		$check = \classes\Auth::verifyPwd(4599, $oUser->PWD_HASH);
		\Mpakfm\Printu::log($check,'verifyPwd 4599 $oUser->PWD_HASH');
		 * 
		 */
		$DB = \classes\Core::init()->db;
		$rsCountries = \classes\Models\Country::GetListExtended([],$arSort=['field'=>'NAME','desc'=>false]);
		$arCountries = [];
		while ($item = $DB->get_row_assoc($rsCountries)) {
			$arCountries[] = $item;
		}
		$this->addTplParams('Countries',$arCountries);
	}
}

