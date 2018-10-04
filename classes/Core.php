<?php
namespace classes;

class Core {
	public static $obj;
	public $db;
	public $router;
	public $i18n;
	public $alerts;
	public $arCfg;
	public $arAccess;
	public $arUsers;
	public $arPages;


	public static function init() {
		return self::$obj ? self::$obj : self::$obj = new Core();
	}
	
	private function __construct() {
		$this->arCfg = Configurator::get('main');
		//Auth::$type = 'Cfg';
		Auth::$type = 'BD';
		$cookie_lng = Router::sanitizeCookie("lng");
		if (!$cookie_lng) $cookie_lng = '';
		$this->i18n = i18n::factory($cookie_lng);
		//$this->arAccess = Configurator::get('access');
		//$this->arUsers = Configurator::get('users');
		//$this->arPages = Configurator::get('pages');
		//$this->alerts = \classes\Alerts\SessionAlerts::init();
		
		if ($this->arCfg['db']) {
			$this->db = Mysql::init($this->arCfg['db']['host'], $this->arCfg['db']['user'], $this->arCfg['db']['password'], $this->arCfg['db']['database']);
			Auth::$type = 'Db';
		}
		
	}
	
	public function render() {
		try {
		
			$this->prolog();

			$this->dispatch();

			$this->epilog();
		} catch(\classes\BaseException $e) {
			\Mpakfm\Printu::log($e->getMsg(),'BaseException');
		} catch (\Exception $e) {
			\Mpakfm\Printu::log($e->getMessage(),'Exception on file '.$e->getFile().'. line: '.$e->getLine());
		}
	}


	private function prolog() {
		$this->router = new Router();
		
		session_start();
		
		Auth::restore();
		if (!isset($_SESSION['AUTH'])) {
			$_SESSION['AUTH'] = false;
		}
		
		// check to first-time login admin and empty pass
		if (isset($this->arUsers[0]['password']) && $this->arUsers[0]['password']=="") {
			Auth::login($this->arUsers[0]);
			if (strpos($this->router->uri,'/admin/') === false) {
				$this->alerts->addMessage(['status'=>'danger','title'=>'Установите пароль для Администратора!','icon'=>'warning','text'=>'Необходимо установить пароль для учетной записи Администратора']);
				Router::Redirect('/admin/');
			}
		}
		
		$action = 'action'.$this->router->action;
		if (!method_exists($this->router->oController, $action))
			$action = 'actionIndex';
		
		
		// Forbidden Controller
		if (!$this->checkAccess()) {
			$this->router = new Router("/forbidden/");
		} else {
			Router::setHeader($this->router->oController->headerCode, $this->router->oController->headerStatus);
		}
		$this->router->oController->addTplParams('ADMIN', Auth::isAdmin());
		$this->router->oController->$action();
		
	}

	private function dispatch() {
		if (isset($_SESSION['USER'])) {
			$this->router->oController->addTplParams('USER',$_SESSION['USER']);
			$this->router->oController->addTplParams('pwd_reqired',(isset($_SESSION['USER']['password']) && $_SESSION['USER']['password']==''));
		}
		/*
		if ($this->alerts->getCount()) {
			$this->router->oController->addTplParams('alerts',$this->alerts->getList([],[],[0,5]));
			$this->router->oController->addTplParams('alertStatus',$this->alerts->arStatusList[$this->alerts->status]);
		}
		 * 
		 */
		$this->router->oController->renderTpl();
	}
	
	private function epilog() {
		
	}
	
	private function checkAccess () {
		
		// По умолчанию доступ есть.
		$bAccess = true;
		
		if (Auth::isAdmin()) return $bAccess;
		
		return $bAccess;
		/*
		// Прямое совпадение пути
		if (in_array($this->router->uri, array_keys($this->arAccess['pages']))) {
			if (!Auth::isLogin()) return false;
			foreach ($this->arAccess['pages'][$this->router->uri] as $group) {
				if (in_array($group, $_SESSION['GROUPS'])) return true;
			}
			return false;
		// Косвенное совпадение по началу. или вообще нет такого урала в доступах
		} else {
			// По умолчанию доступ есть.
			$bAccess = true;
			// прогоняем все ключи доступа
			foreach (array_keys($this->arAccess['pages']) as $access_uri) {
				// Если совпала часть адреса
				if (strpos($this->router->uri, $access_uri) !== false) {
					// Нет логина - сразу нафиг.
					if (!Auth::isLogin()) return false;
					// Проходим по группам которым туда можно
					foreach ($this->arAccess['pages'][$this->router->uri] as $group) {
						// Если у Юзера нет этой группы - запишем FALSE
						// Вдруг по другим ключам доступ будет потому сразу не выходим
						if (!in_array($group, $_SESSION['GROUPS'])) $bAccess = false;
						// На совпадение групп проверяем тока если уже нет доступа. 
						// Что бы если группы сойдутся, то дать доступ.
						if (!$bAccess && in_array($group, $_SESSION['GROUPS'])) $bAccess = true;
					}
				}
			}
		}
		return $bAccess;
		 * 
		 */
	}
	
	public static function checkClass($class) {
		$arPath = explode("\\", $class);
		if (!file_exists(DIR. '/' . implode('/', $arPath) . '.php')) return false;
		return true;
	}
}