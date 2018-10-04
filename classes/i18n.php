<?php

namespace classes;

/**
 * Работа с Интернационализацией.
 * 
 * Запуск компилятора: 
 * mpakfm@uhp:/var/www/mpcms/language/ru_RU/LC_MESSAGES$ msgfmt ru_RU.po -o ru_RU.mo
 */
class i18n {
	
	public static $default = "ru_RU";
	public $locale = null;
	public $path = './language/';
	
	public $arLanguages = [
		'ru_RU'=>'Русский',
		'en_EN'=>'English'
	];
	
	public static function factory($locale = '') {
		if ($locale == '') $locale = self::$default;
		return new i18n($locale);
	}
	
	public function __construct($locale = '') {
		if (!in_array($locale, array_keys($this->arLanguages))) {
			$locale = self::$default;
		}
		$this->locale = $locale;

		putenv("LC_ALL=" . $this->locale); 
		setlocale(LC_ALL, $this->locale, $this->locale . '.utf8');
		bind_textdomain_codeset($this->locale, 'UTF-8');
		bindtextdomain($this->locale, $this->path);
		textdomain($this->locale);
	}
}

