<?php

namespace classes\Models;

class Team extends \classes\Model {
	
	public static $sTable = 'team';
	
	/**
	 * ID
	 * @var int
	 */
	private $ID;
	private $COUNTRY_ID;
	private $NAME;
	private $ORIGINAL_NAME;
	private $CODE;
	private $ALIASES;
	
	
	public static $arDefault = [
		'ID' => null,
		'COUNTRY_ID' => null,
		'NAME' => null,	
		'ORIGINAL_NAME' => null,	
		'CODE' => null,
		'ALIASES' => null,
	];
	
	public function __set($name, $value) {
		if (!array_key_exists($name,self::$arDefault)) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
		switch ($name) {
			case"ID": case"COUNTRY_ID": 
				$value = (int)$value;
				if (!$value) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
			break;
			case"NAME":
				if (!is_string($value)) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
				$value = trim($value);
				if ($value=='') throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
			break;
			default:
				$value = trim($value);
		}
		$this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
	
	public function Delete() {
		if (!$this->ID) throw new \classes\BaseException('NOT_FOUND');
		
		$cnt = self::GetCntGames($this->ID);
		if ($cnt) throw new \classes\BaseException('Нельзя удалить команду, участвует в играх');
		
		$DB = \classes\Core::init()->db;
		GameTeam::DeleteByTeamId($this->ID);
		
		$sql = "DELETE FROM team_parser WHERE TEAM_ID = '{$this->ID}'";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		$sql = "DELETE FROM championship_team WHERE TEAM_ID = '{$this->ID}'";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		
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
//			if (is_null($this->$field)) continue;
			$aFields[] = $field;
			if (is_null($this->$field))
				$aVal[] = 'NULL';
			else
				$aVal[] = "'".str_replace("'","",$this->$field)."'";
		}
		$sql = "INSERT INTO ". self::$sTable. " (`".implode("`,`",$aFields)."`) VALUES (".implode(",",$aVal).")";
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
		return new Team($item);
	}
	public static function GetByAlias($search,$COUNTRY_ID) {
		if ($search == '') return null;
		$sSelect = implode(',',array_keys(self::$arDefault));
		
		$sql = "SELECT ".$sSelect." FROM ". self::$sTable. " WHERE COUNTRY_ID = {$COUNTRY_ID} AND NAME LIKE '".$search."'  LIMIT 1";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		if ($DB->num_rows($rs)) {
			$item = $DB->get_row_assoc($rs);
			return new Team($item);
		} else return null;
	}
	public static function GetByName($search,$COUNTRY_ID) {
		if ($search == '') return null;
		$sSelect = implode(',',array_keys(self::$arDefault));
		
		$sql = "SELECT ".$sSelect." FROM ". self::$sTable. " WHERE COUNTRY_ID = {$COUNTRY_ID} AND NAME LIKE '%".$search."%'";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		if ($DB->num_rows($rs)) {
			$arResult = [];
			while($item = $DB->get_row_assoc($rs)) {
				$arResult[] = $item;
			}
			return $arResult;
		} else return null;
	}
	
	public static function GetByAliases($search,$parser_code,$data) {
		if ($search == '') return null;
		$sSelect = 't.'.implode(',t.',array_keys(self::$arDefault));
		
		$sql = "SELECT ".$sSelect.", tp.CODE FROM ". self::$sTable. " t "
. "LEFT JOIN team_parser tp ON tp.TEAM_ID = t.ID AND tp.CHY_ID = '{$data['CHY_ID']}' AND tp.PARSER_ID = '".$data['PARSER_ID']."'"
. "WHERE t.COUNTRY_ID = {$data['COUNTRY_ID']} AND t.NAME LIKE '%".$search."%' OR t.ALIASES LIKE '%".$search."%'";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		$arResult = [];
		if (!$DB->num_rows($rs)) return $arResult;
		while ($item = $DB->get_row_assoc($rs)) {
			$arResult[] = $item;
		}
		return $arResult;
	}
	
	public static function GetByCode($code, $parser_id, $chy_id) {
		if ($code == '' || !(int)$parser_id) return null;
		$sSelect = 't.'.implode(',t.',array_keys(self::$arDefault));
		
		$sql = "SELECT ".$sSelect.", tp.CODE FROM ". self::$sTable. " t "
. "LEFT JOIN team_parser tp ON tp.TEAM_ID = t.ID AND tp.CHY_ID = '{$chy_id}' AND tp.PARSER_ID = '".$parser_id."'"
. "WHERE tp.CODE = '{$code}' AND tp.CHY_ID = '".$chy_id."' AND tp.PARSER_ID =  '".$parser_id."' LIMIT 1";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		if (!$DB->num_rows($rs)) return false;
		return $DB->get_row_assoc($rs);
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
					$sOrder = 'ORDER BY t.NAME '.($arSort['desc']?'DESC':'ASC');
					break;
				case"ORIGINAL_NAME":
					$sOrder = 'ORDER BY t.ORIGINAL_NAME '.($arSort['desc']?'DESC':'ASC');
					break;
				case"ALIASES":
					$sOrder = 'ORDER BY t.ALIASES '.($arSort['desc']?'DESC':'ASC');
					break;
				case"CODE":
					$sOrder = 'ORDER BY t.CODE '.($arSort['desc']?'DESC':'ASC');
					break;
				case"COUNTRY_ID":
					$sOrder = 'ORDER BY t.COUNTRY_ID '.($arSort['desc']?'DESC':'ASC');
					break;
				case"ID":default:
					$sOrder = 'ORDER BY t.ID '.($arSort['desc']?'DESC':'ASC');
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
		
		$sql = "SELECT t.".implode(', t.', array_keys(self::$arDefault)).", c.CODE as COUNTRY_CODE, c.NAME as COUNTRY_NAME
FROM ". self::$sTable." t\n".
"INNER JOIN country c ON t.COUNTRY_ID = c.ID \n".
$sWhere."\n".
$sOrder."\n".
$sLimit;
		$rs = $DB->query($sql);
		return $rs;
	}
	
	public static function GetCntGames($id) {
		if (!(int)$id) return false;
		$DB = \classes\Core::init()->db;
		$sql = "SELECT COUNT(gt.TEAM_ID) as CNT FROM game_team gt WHERE gt.TEAM_ID = '{$id}'";
		$rs = $DB->query($sql);
		if (!$DB->num_rows($rs)) return 0;
		$ar = $DB->get_row_assoc($rs);
		return $ar['CNT'];
	}
	
	public static function UpdateParserCode($chy_id, $cmd_id, $parser_id, $code) {
		
		$DB = \classes\Core::init()->db;
		$sql = "SELECT ID, CODE FROM team_parser WHERE CHY_ID = '{$chy_id}' AND TEAM_ID = '{$cmd_id}' AND PARSER_ID = '{$parser_id}'";
		$rs = $DB->query($sql);
		if ($DB->num_rows($rs)) {
			$item = $DB->get_row_assoc($rs);
			if ($code != $item['CODE']) {
				$sql_upd = "UPDATE team_parser SET CODE = '{$code}' WHERE ID = '{$item['ID']}'";
				$DB->query($sql_upd);
				return 2;
			}
		} else {
			$sql_ins = "INSERT INTO team_parser (TEAM_ID,CHY_ID,PARSER_ID,CODE) VALUES ('{$cmd_id}','{$chy_id}','{$parser_id}','{$code}')";
			$DB->query($sql_ins);
			return 1;
		}
		return 0;
	}
	public static function DeleteParserCode($chy_id, $cmd_id, $parser_id) {
		
		$DB = \classes\Core::init()->db;
		$sql = "SELECT ID, CODE FROM team_parser WHERE CHY_ID = '{$chy_id}' AND TEAM_ID = '{$cmd_id}' AND PARSER_ID = '{$parser_id}'";
		$rs = $DB->query($sql);
		if ($DB->num_rows($rs)) {
			$item = $DB->get_row_assoc($rs);
			$sql_upd = "DELETE FROM team_parser WHERE ID = '{$item['ID']}'";
			$DB->query($sql_upd);
			return 2;
		}
		return 0;
	}
	
}