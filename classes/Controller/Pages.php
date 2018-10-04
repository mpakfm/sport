<?php

namespace classes\Controller;

class Pages extends \classes\Controller {
	
	public function actionIndex() {
		
		$arPage = $this->getPageByUrl(\classes\Core::init()->router->uri);
		$page = [
			'code'=>$arPage['code'],
			'url'=>$arPage['url'],
			'menu'=>$arPage['menu'],
			'static'=>$arPage['static'],
			'lang'=>$arPage[\classes\Core::init()->i18n->locale],
			'tpl'=>\classes\Core::init()->i18n->locale.'/'.$arPage['code'].'.twig'
		];
		
		$model = 'classes\\Models\\'.ucfirst($arPage['code']);
		if (\classes\Core::checkClass($model)) {
			$oModel = new $model();
			$page['data'] = $oModel->data_lng;
		}
		$this->setRenderTplLng('static.twig');
		$this->addTplParams('page',['title'=>$page['lang']['name']]);
		$this->addTplParams('item',$page);
	}
	
	public function actionAjax() {
		header("Content-type:application/json");
		$this->setRenderTpl('json/message.twig');
		$arResponse = [
			'post'=>$_POST,
			'msg'=>'',
			'error'=>false,
			'error_msg'=>''
		];
		try{
			if (!isset($_POST['name']) || $_POST['name'] == '') 
				throw new \classes\BaseException('Error field Name');
			if (!isset($_POST['email']) || $_POST['email'] == '') 
				throw new \classes\BaseException('Error field Email');
			if (!isset($_POST['message']) || $_POST['message'] == '') 
				throw new \classes\BaseException('Error field Message');
			$message = "FROM: {$_POST['name']} <{$_POST['email']}>\r\n{$_POST['message']}";
			mail('mpakfm@gmail.com','Message from mpakfm.ru',$message);
			$arResponse['msg'] = 'Сообщение отправлено';
		} catch (\classes\BaseException  $ex) {
			$arResponse['error'] = true;
			$arResponse['error_msg'] = 'Ошибка: '.$ex->getMsg();
		}

		$this->addTplParams('response', $arResponse);
	}
	
	private function getPageByUrl($url) {
		
		foreach (\classes\Core::init()->arPages as $key => $arPage) {
			if ($arPage['url'] == $url) return $arPage;
		}
		return false;
	}
	
}

