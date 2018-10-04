<?php

namespace classes\Controller;

class NotFound extends \classes\Controller {
	
	public function actionIndex() {
		$this->headerCode = 404;
		$this->headerStatus = 'Not Found';
		$this->setRenderTplLng('404.twig');
		$this->addTplParams('page',['title'=>'Ошибка 404. Страница не найдена']);
		
	}
}

