<?php

namespace classes\Alerts;

class MysqlAlerts extends classes\Alerts {
	
	public static function init() {
		return self::$obj ? self::$obj : self::$obj = new MysqlAlerts();
	}
	
	public function addMessage($arMsg=[]) {
		
	}
	
	public function setStatus() {
		
	}
}
