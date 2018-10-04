<?php

namespace classes;

class BaseException extends \Exception {
	
	public $arCodes = [
		"NOT_FOUND"=>"Не найдено",
		"SAVE_ERROR"=>"Ошибка сохранения",
		"FORMAT_ERROR"=>"Неверный формат",
		"PARAMS_ERROR"=>"Неверные параметры",
		"UNKNOWN_ERROR"=>"Неизвестная ошибка",
		"DB_ERROR"=>"Ошибка Базы данных",
		"AUTH_ERROR"=>"Ошибка авторизации",
		
		"FIELD_WRONG"=>"Неверное поле: ",
		"FIELD_FORMAT_ERROR"=>"Неверный формат поля: ",
	];
	
	public function __construct($message, $code = 4096, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
	
	/**
	 * Return message with i18n
	 * @param boolean $forAjax
	 * @return string
	 */
	public function getMsg() {
		$with_parametrs = strpos($this->message, '%');
		if ($with_parametrs!==false) {
			$code = substr($this->message, 0,$with_parametrs);
			$msg = $this->convert($code). str_replace([$code,'%'], ['',''], $this->message);
		} else {
			$msg = $this->convert($this->message);
		}
		$msg = "Ошибка #{$this->getCode()} {$msg} в файле {$this->getFile()} на линии {$this->getLine()}";
		return $msg;
	}
	
	public function convert($code) {
		return (isset($this->arCodes[$code])?$this->arCodes[$code]:$code);
	}
}

