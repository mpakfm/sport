<?php

namespace classes\Models;

class ChampionshipTeam extends \classes\Model {
	
	public static $sTable = 'championship_team';
	
	/**
	 * ID
	 * @var int
	 */
	private $ID;
	private $CHY_ID;
	private $TEAM_ID;
	private $NUMBER;
	
	
	public static $arDefault = [
		'ID' => null,
		'CHY_ID' => null,
		'TEAM_ID' => null,
		'NUMBER' => null
	];
	
	public function __set($name, $value) {
		if (!array_key_exists($name,self::$arDefault)) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
		switch ($name) {
			case"ID": case"CHY_ID": case"TEAM_ID": 
				$value = (int)$value;
				if (!$value) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
			break;
			case"NUMBER":
				$value = (int)$value;
				if (!$value) $value = self::$arDefault['NUMBER'];
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
		$sql = "DELETE FROM ". self::$sTable. " WHERE ID = ".$this->ID;
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
		return new ChampionshipTeam($item);
	}
	
	public static function GetListCnt($arFilter=[]) {
		$sWhere = '';
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
		$DB = \classes\Core::init()->db;
		
		$sql = "SELECT COUNT(cht.ID) as CNT
FROM ". self::$sTable." cht\n".
"LEFT JOIN team_parser tp ON tp.CHY_ID = cht.CHY_ID AND tp.TEAM_ID = cht.TEAM_ID\n".
$sWhere;
		$rs = $DB->query($sql);
		$item = $DB->get_row_assoc($rs);
		return $item['CNT'];
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
				case"CHY_ID":
					$sOrder = 'ORDER BY cht.CHY_ID '.($arSort['desc']?'DESC':'ASC');
					break;
				case"TEAM_ID":
					$sOrder = 'ORDER BY cht.TEAM_ID '.($arSort['desc']?'DESC':'ASC');
					break;
				case"NUMBER":
					$sOrder = 'ORDER BY cht.NUMBER '.($arSort['desc']?'DESC':'ASC');
					break;
				case"ID":default:
					$sOrder = 'ORDER BY cht.ID '.($arSort['desc']?'DESC':'ASC');
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
		
		$sql = "SELECT cht.".implode(', cht.', array_keys(self::$arDefault)).", tp.CODE as TP_CODE
FROM ". self::$sTable." cht\n".
"LEFT JOIN team_parser tp ON tp.CHY_ID = cht.CHY_ID AND tp.TEAM_ID = cht.TEAM_ID\n".
$sWhere."\n".
$sOrder."\n".
$sLimit;
		$rs = $DB->query($sql);
		return $rs;
	}
	
}