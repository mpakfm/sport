<?php

namespace classes;


abstract class Model {
	
	public static $sTable;
	
	public static $arDefault=[];
	
	public function __construct($arFields=[]) {
		if (is_array($arFields) && !empty($arFields))
		foreach ($arFields as $field=>$value) {
			$this->$field = $value;
		}
		foreach (self::$arDefault as $field=>$value) {
			if (!isset($this->$field))
				$this->$field = $value;
		}
	}
	
	abstract public function __set($name, $value);
	
	abstract public function __get($name);

	public function Save() {
		if (is_null($this->ID)) return $this->insert();
		else return $this->update();
	}
	
	abstract function Delete();
	
	abstract function Insert();
	
	abstract function Update();
	
	abstract public static function GetList($arFilter=[], $arSort=['ID'=>'ASC'], $arLimit=[]);
	
	abstract public static function GetById($ID=0);

}

