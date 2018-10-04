<?php

namespace classes\Models;

class Championship extends \classes\Model {
	
	public static $sTable = 'championship';
	
	/**
	 * ID
	 * @var int
	 */
	private $ID;
	private $NAME;
	private $CODE;
	private $COUNTRY_ID;
	private $PARSER_ID;
	private $OUTER_CODE;
	
	
	public static $arDefault = [
		'ID' => null,
		'NAME' => null,
		'CODE' => null,		
		'COUNTRY_ID' => null,		
		'PARSER_ID' => null,		
		'OUTER_CODE' => null	
	];
	
	public function __set($name, $value) {
		if (!array_key_exists($name,self::$arDefault)) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
		switch ($name) {
			case"ID": case"COUNTRY_ID": 
				$value = (int)$value;
				if (!$value) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
			break;
			case"NAME":case"CODE":
				if (!is_string($value)) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
				$value = trim($value);
				if ($value=='') throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
			break;
			default:
		}
		$this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
	
	public function Delete() {
		if (!$this->ID) throw new \classes\BaseException('NOT_FOUND');
		$DB = \classes\Core::init()->db;
		$sql = "DELETE FROM ". self::$sTable. " WHERE ID = ".$ID;
		$DB->query($sql);
		if ($DB->affected_rows()) {
			foreach (self::$arDefault as $field=>$value) {
				$this->$field = $value;
			}
			return $this;
		}
		else throw new \classes\BaseException('DB_ERROR');
	}
	
	public function Insert() {
		$DB = \classes\Core::init()->db;
		foreach (self::$arDefault as $field=>$value) {
			if (is_null($this->$field)) continue;
			$aFields[] = $field;
			$aVal[] = str_replace("'","",$this->$field);
		}
		$sql = "INSERT INTO ". self::$sTable. " (`".implode("`,`",$aFields)."`) VALUES ('".implode("','",$aVal)."')";
		$rs = $DB->query($sql);
		$insert_id = $DB->insert_id();
		if (!$insert_id) {
			throw new \classes\BaseException('DB_ERROR');
		}
		$this->ID = $insert_id;
		return $this;
	}
	
	public function Update() {
		$DB = \classes\Core::init()->db;
		foreach (self::$arDefault as $field=>$value) {
			if ($field=='ID' || is_null($this->$field)) continue;
			
			$aFields[] = $field." = '".str_replace("'","",$this->$field)."'";
		}
		$sql = "UPDATE ". self::$sTable. " SET ".implode(',',$aFields)." WHERE ID = ".$this->ID;
		$DB->query($sql);
		if (!$DB->affected_rows()) throw new \classes\BaseException('DB_ERROR');
		return $this;
	}
	
	public static function GetById($ID=0) {
		if (!(int)$ID) return null;
		$sSelect = implode(',',array_keys(self::$arDefault));
		
		$sql = "SELECT ".$sSelect." FROM ". self::$sTable. " WHERE ID = ".$ID." LIMIT 1";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		$item = $DB->get_row_assoc($rs);
		return new Championship($item);
	}
	
	public static function GetList($arFilter=[], $arSort=['field'=>'ID','desc'=>false], $arLimit=[]) {
		$sWhere = '';
		$sOrder = '';
		$sLimit = '';
		if (!empty($arFilter)) {
			$addWhere = [];
			foreach ($arFilter as $f=>$arr) {
				if ($arr['value'] === null) $arr['value'] = 'NULL';
				else $arr['value'] = "'".$arr['value']."'";
				$addWhere[] = $arr['table'].'.'.$f.' '.$arr['type']." ".$arr['value'];
			}
			if (!empty($addWhere)) {
				$sWhere = "WHERE ".implode(' AND ',$addWhere);
			}
		}
		
		if (!empty($arSort)) {
			switch ($arSort['field']) {
				case"NAME":
					$sOrder = 'ORDER BY ch.NAME '.($arSort['desc']?'DESC':'ASC');
					break;
				case"COUNTRY_ID":
					$sOrder = 'ORDER BY ch.COUNTRY_ID '.($arSort['desc']?'DESC':'ASC');
					break;
				case"CODE":
					$sOrder = 'ORDER BY ch.CODE '.($arSort['desc']?'DESC':'ASC');
					break;
				case"ID":default:
					$sOrder = 'ORDER BY ch.ID '.($arSort['desc']?'DESC':'ASC');
					break;
			}
		}
		
		if (!empty($arLimit)) {
			if (isset($arLimit['count']) && (int)$arLimit['count'] > 0) {
				$arLimit['count'] = (int)$arLimit['count'];
				$arLimit['start'] = (int)$arLimit['start'];
				$sLimit .= "LIMIT ".$arLimit['start'].', '.$arLimit['count'];
			}
		}
		
		$DB = \classes\Core::init()->db;
		
		$sql = "SELECT ch.".implode(', ch.', array_keys(self::$arDefault)).", chy.SEASONS, c.CODE as COUNTRY_CODE, c.ID as COUNTRY_ID, p.ID as PARSER_ID, p.NAME as PARSER_NAME
FROM ". self::$sTable." ch\n".
"INNER JOIN country c ON ch.COUNTRY_ID = c.ID \n".
"LEFT JOIN parser p ON p.ID = ch.PARSER_ID \n".
"LEFT JOIN (
    SELECT chy.CH_ID, COUNT(chy.ID) as SEASONS FROM championship_year chy GROUP BY chy.CH_ID
) chy ON chy.CH_ID = ch.ID\n".
$sWhere."\n".
$sOrder."\n".
$sLimit;
		$rs = $DB->query($sql);
		return $rs;
	}
	
}