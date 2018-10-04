<?php

namespace classes\Models;

class Game extends \classes\Model {
	
	public static $sTable = 'game';
	
	/**
	 * ID
	 * @var int
	 */
	private $ID;
	private $NAME;
	private $CHY_ID;
	private $GAME_DATE;
	
	
	public static $arDefault = [
		'ID' => null,
		'NAME' => null,
		'CHY_ID' => null,		
		'GAME_DATE' => null,		
	];
	
	public function __set($name, $value) {
		if (!array_key_exists($name,self::$arDefault)) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
		switch ($name) {
			case"ID": case"CHY_ID": 
				$value = (int)$value;
				if (!$value) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
			break;
			case"GAME_DATE":
				\Mpakfm\Printu::log($value,'GAME_DATE $value','file');
				if (is_null($value)) {
					$value = new \DateTime();
				}
				elseif (is_string($value)) {
					$value = date_create_from_format('Y-m-d',$value);
				}
				elseif (is_int($value)) {
					$value = date_create_from_format('U',$value);
				}
				if (!($value instanceof \DateTime)) {
					throw new \classes\BaseException('FIELD_FORMAT_ERROR%'.$name.'%');
				}
			break;
			case"NAME":
				if (!is_string($value)) $value = self::$arDefault['NAME'];
				$value = trim($value);
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
			if ($field=='GAME_DATE') {
				$value = $this->GAME_DATE->format('Y-m-d');
			} else {
				$value = $this->$field;
			}
			$aFields[] = $field;
			$aVal[] = str_replace("'","",$value);
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
			if ($field=='GAME_DATE') {
				$value = $this->GAME_DATE->format('Y-m-d');
			} else {
				$value = $this->$field;
			}
			$aFields[] = $field." = '".str_replace("'","",$value)."'";
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
		return new Game($item);
	}
	public static function GetByIdParser($ID=0,$PARSER_ID=0) {
		if (!(int)$ID || !(int)$PARSER_ID) return null;
		$sSelect = "g.".implode(',g.',array_keys(self::$arDefault));
		
		$sql = "SELECT ".$sSelect.", gp.CODE FROM ". self::$sTable. " g
INNER JOIN game_parser gp ON gp.GAME_ID = g.ID 
WHERE g.ID = ".$ID." AND gp.PARSER_ID = ".$PARSER_ID." LIMIT 1";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		return $DB->get_row_assoc($rs);
	}
	public static function GetByCodeParser($CODE,$PARSER_ID=0) {
		if (!$CODE || !(int)$PARSER_ID) return null;
		$sSelect = "g.".implode(',g.',array_keys(self::$arDefault));
		
		$sql = "SELECT ".$sSelect.", gp.CODE FROM ". self::$sTable. " g
INNER JOIN game_parser gp ON gp.GAME_ID = g.ID 
WHERE gp.CODE = ".$CODE." AND gp.PARSER_ID = ".$PARSER_ID." LIMIT 1";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		return $DB->get_row_assoc($rs);
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
		
		$sql = "SELECT COUNT(g.ID) as CNT
FROM ". self::$sTable." g\n".
"INNER JOIN game_team gth ON g.ID = gth.GAME_ID AND gth.HOME = 1 \n".
"INNER JOIN team th ON gth.TEAM_ID = th.ID \n".
"INNER JOIN game_team gtg ON g.ID = gtg.GAME_ID AND gtg.HOME = 0 \n".
"INNER JOIN team tg ON gtg.TEAM_ID = tg.ID \n".
$sWhere;
		$rs = $DB->query($sql);
		$ar = $DB->get_row_assoc($rs);
		return $ar['CNT'];
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
					$sOrder = 'ORDER BY g.NAME '.($arSort['desc']?'DESC':'ASC');
					break;
				case"CHY_ID":
					$sOrder = 'ORDER BY g.CHY_ID '.($arSort['desc']?'DESC':'ASC');
					break;
				case"GAME_DATE":
					$sOrder = 'ORDER BY g.GAME_DATE '.($arSort['desc']?'DESC':'ASC');
					break;
				case"ID":default:
					$sOrder = 'ORDER BY g.ID '.($arSort['desc']?'DESC':'ASC');
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
		
		$sql = "SELECT g.".implode(', g.', array_keys(self::$arDefault)).",
gth.WIN as H_WIN, gth.DRAW as H_DRAW, gth.SCORE as H_SCORE,
gtg.WIN as G_WIN, gtg.DRAW as G_DRAW, gtg.SCORE as G_SCORE,
th.NAME as H_CMD, tg.NAME as G_CMD
FROM ". self::$sTable." g\n".
"INNER JOIN game_team gth ON g.ID = gth.GAME_ID AND gth.HOME = 1 \n".
"INNER JOIN team th ON gth.TEAM_ID = th.ID \n".
"INNER JOIN game_team gtg ON g.ID = gtg.GAME_ID AND gtg.HOME = 0 \n".
"INNER JOIN team tg ON gtg.TEAM_ID = tg.ID \n".
$sWhere."\n".
$sOrder."\n".
$sLimit;

		$rs = $DB->query($sql);
		return $rs;
	}
	
	public static function UpdateParserCode($game_id, $parser_id, $code) {
		
		$DB = \classes\Core::init()->db;
		$sql = "SELECT ID, CODE FROM game_parser WHERE CODE = '{$code}' AND PARSER_ID = '{$parser_id}'";
		$rs = $DB->query($sql);
		if ($DB->num_rows($rs)) {
			$item = $DB->get_row_assoc($rs);
			if ($game_id != $item['GAME_ID']) {
				$sql_upd = "UPDATE game_parser SET GAME_ID = '{$game_id}' WHERE ID = '{$item['ID']}'";
				$DB->query($sql_upd);
				return 2;
			}
		} else {
			$sql_ins = "INSERT INTO game_parser (GAME_ID,PARSER_ID,CODE) VALUES ('{$game_id}','{$parser_id}','{$code}')";
			$DB->query($sql_ins);
			return 1;
		}
		return 0;
	}
	
}