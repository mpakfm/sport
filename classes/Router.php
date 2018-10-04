<?php

namespace classes;

class Router {
	public $PATH;
	public $GET;
	public $POST;
	
	public $arPathController = [];
	public $oController;
	public $action;

	public $scheme;
	public $host;
	public $user_agent;
	public $http_lng;
	public $http_encoding;
	public $name;
	public $filename;
	public $uri;
	public $method;
	public $query='';
	
	public $cookie_domain;
	public $cookie_secure = false;
	public $cookie_httponly = true;


	public function __construct($path=false) {
		//\Mpakfm\Printu::log($_SERVER,'server','file');
		$this->scheme = $_SERVER['REQUEST_SCHEME'];
		$this->host = $_SERVER['HTTP_HOST'];
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$this->http_lng = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$this->http_encoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
		$this->name = $_SERVER['SERVER_NAME'];
		$this->filename = $_SERVER['SCRIPT_FILENAME'];
		
		$this->cookie_domain = $this->host;
		if ($this->scheme == 'https') $this->cookie_secure = true;
		
		if (!$path) {
			$this->uri = $_SERVER['REQUEST_URI'];
			$this->method = $_SERVER['REQUEST_METHOD'];
			$this->query = $_SERVER['QUERY_STRING'];
		} else {
			$__tmp = explode('?', $path);
			$this->uri = $__tmp[0];
			$this->query = (isset($__tmp[1])?$__tmp[1]:'');
		}
		// clear query string
		if ($this->query != '') {
			$this->uri = str_replace('?'.$this->query, '', $this->uri);
		}
		// If Home
		if ($this->uri == '/') {
			$PATH = ['/'];
		} else {
			$PATH = explode('/', substr($this->uri,1));
		}
		// clear empty
		foreach ($PATH as $elem) {
			if (!$elem || $elem=='') continue;
			$this->PATH[]=$elem;
		}
		
		// Language check path by lang_list
		if (Core::init()->arCfg['i18n']) {
			
		}
		// Set Controller
		//$this->arPathController[] = CLASSES;
		$this->setController();
		// Check query params
		$this->sanitize();
	}
	
	private function sanitize() {
		$this->GET = (!empty($_GET)?$_GET:false);
		$this->POST = (!empty($_POST)?$_POST:false);
	}
	
	public static function sanitizeCookie($name) {
		return filter_input(INPUT_COOKIE, $name, FILTER_SANITIZE_FULL_SPECIAL_CHARS,FILTER_FLAG_NO_ENCODE_QUOTES);
	}
	public static function sanitizeEmail($email,$post=true) {
		return filter_input($post?INPUT_POST:INPUT_GET,$email,FILTER_SANITIZE_EMAIL);
	}
	public static function sanitizeString($name,$post=true) {
		return filter_input($post?INPUT_POST:INPUT_GET,$name,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	}
	
	private function setController() {
		global $oCore;
		$arStaticUrls = $this->getStaticUrls();
		// Route Rules
		if (in_array($this->PATH[0], array_keys($oCore->arCfg['route']))) {
			$Rule = $oCore->arCfg['route'][$this->PATH[0]];
			$this->readRule($Rule);
		}
		// Static Urls
		elseif (in_array($this->uri, $arStaticUrls)) {
			$this->oController = 'Pages';
			$this->action = "Index";
		}
		// default: Controller -> Action
		else {
			$this->oController = $this->PATH[0];
			$this->action = (isset($this->PATH[1])?$this->PATH[1]:"Index");
		}
		$this->oController = ucfirst($this->oController);
		$sRealPath = CLASSES.'/Controller/'.implode('/', $this->arPathController).(!empty($this->arPathController)?'/':'').$this->oController.'.php';
		
		if (!file_exists($sRealPath)) {
			$sRealPath = $this->Set404();
		}
		$this->loadController($sRealPath);
	}
	
	private function loadController($sRealPath) {
		require_once $sRealPath;
		$ctrlName = 'classes\\Controller\\'.(!empty($this->arPathController)?implode('\\',$this->arPathController).'\\':'').$this->oController;
		$this->oController = new $ctrlName();
	}
	
	private function getStaticUrls() {
		$arPages = [];
		//$arPages = \classes\Configurator::get('pages');
		$arStaticUrls = [];
		if (!$arPages || empty($arPages)) return $arStaticUrls;
		foreach ($arPages as $page) {
			$arStaticUrls[] = $page['url'];
		}
		return $arStaticUrls;
	}

	private function readRule($rule) {
		foreach ($rule as $key => $value) {
			if (is_array($value)) {
				if ($key != '/')
					$this->arPathController[] = ucfirst($key);
				$i = count($this->arPathController);
				if (!isset($this->PATH[$i])) {
					$this->readRule($value);
					return;
				} else
				// Если есть совпадение дальше - читаем дальше.
				if (isset($value[$this->PATH[$i]]))
					$this->readRule($value);
				else {
					$this->oController = $this->PATH[$i];
					$this->action = (isset($this->PATH[($i+1)])?$this->PATH[($i+1)]:"Index");
				}
			} else {
				$this->oController = $key;
				$this->action = $value;
			}
		}
	}


	public function Set404() {
		$this->arPathController = [];
		$this->oController = 'NotFound';
		$this->action = 'Index';
		return CLASSES.'/Controller/'.$this->oController.'.php';
	}
	
	public static function setHeader($code,$status) {
		header("HTTP/1.0 ".$code." ".$status);
		header("HTTP/1.1 ".$code." ".$status);
		header("Status: ".$code." ".$status);
	}
	
	public static function Redirect($path) {
		header("Location: ".$path);
		die;
	}
}

