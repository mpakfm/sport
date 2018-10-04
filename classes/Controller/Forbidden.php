<?php

namespace classes\Controller;

class Forbidden extends \classes\Controller {
	
	public function actionIndex() {
		$this->headerCode = 403;
		$this->headerStatus = 'Forbidden';
		$this->setRenderTplLng('forbidden.twig');
		$this->addTplParams('page',['title'=>'Ошибка 403. Доступ запрещён']);
		$loginPanel = new \classes\Controller\Widget\Login();
		$this->addTplParams('panel',$loginPanel->actionIndex());
	}
}

