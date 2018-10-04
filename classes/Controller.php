<?php

namespace classes;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Controller {
	public $twig;
	public $headerCode = '200';
	public $headerStatus = 'OK';
	private $template; 
	private $tplVars = [];
	
	public function __construct() {
		//$loader = new FilesystemLoader(TPL.'/'.Core::init()->i18n->locale);
		$loader = new FilesystemLoader(TPL);
		
		$this->twig = new Environment($loader, array(
			//'cache' => DIR.'/tpl_cache',
		));
		$this->twig->addExtension(new \Twig_Extensions_Extension_I18n());
		$SITE = [
			'name'=>Core::init()->arCfg['name'],
			'version'=>Core::init()->arCfg['version'],
			'lang'=>Core::init()->arCfg['site']['lang'],
			'meta_title'=>Core::init()->arCfg['site']['meta']['title'],
			'meta_description'=>Core::init()->arCfg['site']['meta']['description'],
			'meta_keywords'=>Core::init()->arCfg['site']['meta']['keywords'],
			'copyright'=>Core::init()->arCfg['site']['copyright']
		];
		$this->addTplParams('SITE',$SITE);
		
		$this->setMenu();
		$this->setLang();
	}
	
	public function setLang() {
		
		$this->addTplParams('CUR_LANG',Core::init()->i18n->locale);
		if (count(Core::init()->i18n->arLanguages)>2) {
			$this->addTplParams('LANG',Core::init()->i18n->arLanguages);
		} else {
			$this->addTplParams('LANG',false);
			foreach (Core::init()->i18n->arLanguages as $code=>$name) {
				if ($code != Core::init()->i18n->locale) {
					$this->addTplParams('NEXT_LANG',$code);
					$this->addTplParams('NEXT_LANG_NAME',$name);
				}
			}
		}
	}
	
	public function setMenu() {
		
		$arMenu = [];
		$locale = Core::init()->i18n->locale;
		/*
		foreach (Core::init()->arPages as $id=>$arPage) {
			if (!$arPage['menu']) continue;
			$arMenu[$arPage['menu_sort']] = [
				'name' => $arPage[$locale]['menu'],
				'code' => $arPage['code'],
				'url' => $arPage['url']
			];
		}
		 * 
		 */
		ksort($arMenu);
		$this->addTplParams('MENU',$arMenu);
	} 
	
	public function setRenderTpl($tpl_name='index.twig') {
		$this->template = $tpl_name;
	}
	public function setRenderTplLng($tpl_name='index.twig') {
		$this->template = Core::init()->i18n->locale.'/'.$tpl_name;
	}
	
	public function addTplParams($name,$value=null) {
		$this->tplVars[$name] = $value;
	}
	
	public function renderTpl($public=true) {
		\Mpakfm\Printu::log(Core::init()->router->uri,'uri','file');
		if (Core::init()->router->uri == '/') {
			$this->addTplParams('HOME_PAGE',true);
		} else {
			$this->addTplParams('HOME_PAGE',false);
		}
		if ($public)
			echo $this->twig->render($this->template,$this->tplVars);
		else
			return $this->twig->render($this->template,$this->tplVars);
	}
	
	public function actionIndex() {
		
	}
}