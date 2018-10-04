<?php

namespace classes\Models;

class ChampionshipYear extends \classes\Model {
	
	public static $sTable = 'championship_year';
	
	/**
	 * ID
	 * @var int
	 */
	private $ID;
	private $CH_ID;
	private $YEAR;
	private $START;
	private $ROUNDS;
	
	
	public static $arDefault = [
		'ID' => null,
		'CH_ID' => null,
		'YEAR' => null,
		'START' => 'spring',
		'ROUNDS' => 2,
	];
	
	public static $arStartEnum = [
		'spring',
		'autumn'
	];
	
	public static $arCountryStart = [
		'1' => 'autumn',
		'2' => 'autumn',
		'3' => 'autumn',
		'4' => 'autumn',
		'5' => 'autumn',
		'6' => 'autumn',
		'7' => 'autumn',
		'8' => 'autumn',
		'9' => 'autumn',
		'10' => 'spring',
		'11' => 'spring',
		'12' => 'autumn',
		'13' => 'autumn',
		'14' => 'autumn',
		'15' => 'spring',
		'16' => 'spring',
		'17' => 'spring',
		'18' => 'spring',
	];
	
	public function __set($name, $value) {
		if (!array_key_exists($name,self::$arDefault)) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
		switch ($name) {
			case"ID": case"CH_ID": case"YEAR": 
				$value = (int)$value;
				if (!$value) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
			break;
			case"START":
				if (!is_string($value) || $value == '') {
					$value = self::$arDefault['START'];
				} else {
					$value = trim($value);
					if (!in_array($value,self::$arStartEnum)) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
				}
			break;
			case"ROUNDS":
				$value = (int)$value;
				if (!$value) $value = self::$arDefault['ROUNDS'];
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
	
	public function GetParseCode($iParserId) {
		if (!$this->ID) throw new \classes\BaseException('NOT_FOUND');
		$iParserId = (int)$iParserId;
		if ($iParserId == '') return false;
		$DB = \classes\Core::init()->db;
		$sql = "SELECT ID, CHY_ID, PARSER_ID, CODE FROM championship_parser WHERE CHY_ID = '{$this->ID}' AND PARSER_ID = '{$iParserId}'";
		$rs = $DB->query($sql);
		$item = $DB->get_row_assoc($rs);
		return $item;
	}
	
	public function SaveParseCode($sCode, $iParserId) {
		if (!$this->ID) throw new \classes\BaseException('NOT_FOUND');
		$iParserId = (int)$iParserId;
		if ($sCode == '' || $iParserId == '') return false;
		
		$DB = \classes\Core::init()->db;
		$item = $this->GetParseCode($iParserId);
		if (!$item) {
			$sql = "INSERT INTO championship_parser (CHY_ID, PARSER_ID, CODE) VALUES ('{$this->ID}','{$iParserId}','{$sCode}')";
			$DB->query($sql);
		} elseif ($item['CODE'] != $sCode) {
			$sql = "UPDATE championship_parser SET CODE = '{$sCode}' WHERE ID = '{$item['ID']}'";
			$DB->query($sql);
		}
		return true;
	}
	
	public static function GetById($ID=0) {
		if (!(int)$ID) return null;
		$sSelect = implode(',',array_keys(self::$arDefault));
		
		$sql = "SELECT ".$sSelect." FROM ". self::$sTable. " WHERE ID = ".$ID." LIMIT 1";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		$item = $DB->get_row_assoc($rs);
		return new ChampionshipYear($item);
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
				case"CH_ID":
					$sOrder = 'ORDER BY chy.CH_ID '.($arSort['desc']?'DESC':'ASC');
					break;
				case"YEAR":
					$sOrder = 'ORDER BY chy.YEAR '.($arSort['desc']?'DESC':'ASC');
					break;
				case"START":
					$sOrder = 'ORDER BY chy.START '.($arSort['desc']?'DESC':'ASC');
					break;
				case"ROUNDS":
					$sOrder = 'ORDER BY chy.ROUNDS '.($arSort['desc']?'DESC':'ASC');
					break;
				case"ID":default:
					$sOrder = 'ORDER BY chy.ID '.($arSort['desc']?'DESC':'ASC');
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
		
		$sql = "SELECT chy.".implode(', chy.', array_keys(self::$arDefault)).", ch.NAME, c.NAME as COUNTRY, c.CODE as COUNTRY_CODE, c.ID as COUNTRY_ID, p.ID as PARSER_ID, p.NAME as PARSER, chp.CODE as P_CODE
FROM ". self::$sTable." chy\n".
"INNER JOIN championship ch ON chy.CH_ID = ch.ID \n".
"INNER JOIN country c ON ch.COUNTRY_ID = c.ID \n".
"LEFT JOIN championship_parser chp ON chp.CHY_ID = chy.ID \n".
"LEFT JOIN parser p ON p.ID = chp.PARSER_ID \n".
$sWhere."\n".
$sOrder."\n".
$sLimit;
		$rs = $DB->query($sql);
		return $rs;
	}
	
}