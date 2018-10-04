<?php

namespace classes;

class Configurator {
	public static $cfg;
	private $path;
	private $arCfg = [];
	

	public static function get($name='main') {
		if (!isset(self::$cfg)) {
			self::$cfg = new Configurator();
		}
		return self::$cfg->$name;
	}
	public static function set($name,$cfg) {
		if (!isset(self::$cfg)) {
			self::$cfg = new Configurator();
		}
		self::$cfg->$name = $cfg;
	}
	
	public static function save($name,$cfg) {
		self::set($name, $cfg);
		self::$cfg->saveConfig($name, $cfg);
	}

	private function __construct() {
		$this->path = DIR.'/cfg/';
		return $this;
	}

	private function getConfig($name) {
		try {
			return json_decode(file_get_contents($this->path.$name.'.json'), true);
		} catch (\Exception $ex) {
			Printu::log($ex->getMessage(),'Error');
		}
	}
	
	public function __get($name) {
		if (isset($this->arCfg[$name]))	return $this->arCfg[$name];
		else {
			$this->arCfg[$name] = $this->getConfig($name);
		}
		return $this->arCfg[$name];
	}
	
	private function saveConfig($name,$cfg) {
		
		try {
			$string = json_encode($cfg);
			$res = file_put_contents($this->path.$name.'.json', $string);
		} catch (\Exception $ex) {
			Printu::log($ex->getMessage(),'Error');
		}
	}
	
	public function __set($name,$cfg) {
		$this->saveConfig($name, $cfg);
		$this->arCfg[$name] = $cfg;
		return true;
	}
}