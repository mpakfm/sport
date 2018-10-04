<?php

namespace classes\Alerts;

class SessionAlerts extends \classes\Alerts {
	
	public static function init() {
		return self::$obj ? self::$obj : self::$obj = new SessionAlerts();
	}
	
	public function getCount($arFilter=[]) {
		if (!isset($_SESSION['ALERTS'])) return 0;
		return count($_SESSION['ALERTS']);
	}
	
	public function getList($arSort=['time'=>'desc'],$arFilter=[],$arLimit=[0,10]) {
		$arResult = [];
		$start = 0;
		foreach ($_SESSION['ALERTS'] as $key=>$msg) {
			if ($arLimit[0]>$start) {
				$start++;
				continue;
			}
			$start++;
			$arMessage = $msg;
			$arMessage['id'] = $key;
			$arResult[] = $arMessage;
			if ($arLimit[1]==count($arResult)) break;
		}
		return $arResult;
	} 
	
	public function addMessage($arMsg=[]) {
		if (empty($arMsg)) return;
		if (!isset($_SESSION['ALERTS'])) $_SESSION['ALERTS'] = [];
		$status = (in_array($arMsg['status'],$this->arStatusList)?$arMsg['status']:$this->sDefaultStatus);
		$key_status = array_search($status, $this->arStatusList);
		$_SESSION['ALERTS'][] = [
			'status'=>$status,
			'title'=>$arMsg['title'],
			'icon'=>(isset($arMsg['icon'])?$arMsg['icon']:false),
			'text'=>$arMsg['text'],
			'time'=>new \DateTime(),
			'read'=>false
		];
		if ($this->status === false || $key_status < $this->status) {
			$this->setStatus($key_status);
		}
	}
	
	public function setStatus($key_status) {
		$this->status = (int)$key_status;
	}
	
	public function setRead($id) {
		if (!isset($_SESSION['ALERTS'][$id])) return;
		$_SESSION['ALERTS'][$id]['read'] = true;
	}
	
	public function deleteMessage($id) {
		if (!isset($_SESSION['ALERTS'][$id])) return;
		unset($_SESSION['ALERTS'][$id]);
	}
}
