<?php

namespace classes\Models;

class GameTeam extends \classes\Model {
	
	public static $sTable = 'game_team';
	
	/**
	 * ID
	 * @var int
	 */
	private $ID;
	private $GAME_ID;
	private $TEAM_ID;
	private $HOME;
	private $WIN;
	private $DRAW;
	private $SCORE;
	
	
	public static $arDefault = [
		'ID' => null,
		'GAME_ID' => null,
		'TEAM_ID' => null,		
		'HOME' => 1,		
		'WIN' => 0,		
		'DRAW' => 0,		
		'SCORE' => null,		
	];
	
	public function __set($name, $value) {
		if (!array_key_exists($name,self::$arDefault)) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
		switch ($name) {
			case"ID": case"GAME_ID": case"TEAM_ID": 
				$value = (int)$value;
				if (!$value) throw new \classes\BaseException('FIELD_WRONG%'.$name.'%');
			break;
			case"HOME": case"WIN": case"DRAW": case"SCORE":
				if (is_null($value)) {
					$value = self::$arDefault[$name];
				} else {
					$value = (int)$value;
				}
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
	
	public static function DeleteByTeamId($ID=0) {
		if (!(int)$ID) return null;
		$sql = "DELETE FROM ". self::$sTable. " WHERE TEAM_ID = '{$ID}'";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		return $DB->affected_rows();
	}
	
	public static function GetById($ID=0) {
		if (!(int)$ID) return null;
		$sSelect = implode(',',array_keys(self::$arDefault));
		
		$sql = "SELECT ".$sSelect." FROM ". self::$sTable. " WHERE ID = ".$ID." LIMIT 1";
		$DB = \classes\Core::init()->db;
		$rs = $DB->query($sql);
		$item = $DB->get_row_assoc($rs);
		return new GameTeam($item);
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
				case"GAME_ID":
					$sOrder = 'ORDER BY gt.GAME_ID '.($arSort['desc']?'DESC':'ASC');
					break;
				case"TEAM_ID":
					$sOrder = 'ORDER BY gt.TEAM_ID '.($arSort['desc']?'DESC':'ASC');
					break;
				case"HOME":
					$sOrder = 'ORDER BY gt.HOME '.($arSort['desc']?'DESC':'ASC');
					break;
				case"WIN":
					$sOrder = 'ORDER BY gt.WIN '.($arSort['desc']?'DESC':'ASC');
					break;
				case"DRAW":
					$sOrder = 'ORDER BY gt.DRAW '.($arSort['desc']?'DESC':'ASC');
					break;
				case"SCORE":
					$sOrder = 'ORDER BY gt.SCORE '.($arSort['desc']?'DESC':'ASC');
					break;
				case"ID":default:
					$sOrder = 'ORDER BY gt.ID '.($arSort['desc']?'DESC':'ASC');
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
		
		$sql = "SELECT gt.".implode(', gt.', array_keys(self::$arDefault))."
FROM ". self::$sTable." gt\n".
$sWhere."\n".
$sOrder."\n".
$sLimit;
		$rs = $DB->query($sql);
		return $rs;
	}
	
	public static function GetPair($cmd1, $cmd2, $exclude=[]) {
		$DB = \classes\Core::init()->db;
		$sql = "SELECT gt1.GAME_ID, gt1.TEAM_ID as T1, gt1.HOME, gt1.WIN, gt1.DRAW, gt1.SCORE as SCORE1, gt2.TEAM_ID as T2, gt2.HOME as HOME2, gt2.WIN as WIN2, gt2.SCORE as SCORE2, g.NAME, g.GAME_DATE
FROM game_team gt1
INNER JOIN game_team gt2 ON gt2.GAME_ID = gt1.GAME_ID AND gt2.TEAM_ID = '{$cmd1}'
INNER JOIN game g ON g.ID = gt1.GAME_ID
WHERE gt1.TEAM_ID = '{$cmd2}'
ORDER BY g.GAME_DATE ASC";
		$rs = $DB->query($sql);
		$arResult = [];
		$B1SCORE = 0;
		$B2SCORE = 0;
		while ($item = $DB->get_row_assoc($rs)) {
			if (in_array($item['GAME_ID'], $exclude)) {
				$item['EX'] = 1;
			} else {
				$item['EX'] = 0;
				if ($item['DRAW']=='1') {
					$item['CMD1_BSCORE'] = 1;
					$item['CMD2_BSCORE'] = 1;
				}
				elseif ($item['WIN']=='1') {
					$item['CMD1_BSCORE'] = 3;
					$item['CMD2_BSCORE'] = -3;
				} else {
					$item['CMD1_BSCORE'] = -3;
					$item['CMD2_BSCORE'] = 3;
				}
				$B1SCORE += $item['CMD1_BSCORE'];
				$B2SCORE += $item['CMD2_BSCORE'];
				$item['B1SCORE'] = $B1SCORE;
				$item['B2SCORE'] = $B2SCORE;
			}
			$arResult[] = $item;
		}
		return $arResult;
	}
	
}