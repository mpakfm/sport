<?php

namespace classes\Controller;

class Parser extends \classes\Controller {
	
	public function actionIndex() {
		$this->setRenderTplLng('parser.twig');
		$this->addTplParams('page',['title'=>'Parser']);
		
		$oParser = new \classes\Lib\Parser();
		//\Mpakfm\Printu::log($oParser);
		
		//$html = $oParser->grabber();
		$file = file_get_contents('upload/test_country.html');
		if (!strlen($file)) die('FILE NOT FOUND');
		$result = $oParser->seasons_parser($file,2);
	}
}

