<?php

namespace classes\Models;

class Parser {
	/**
	 * Получить все данные по чемпионату по $iParserId и коду сезона
	 * @param type $iParserId
	 * @param type $SeasonCode 
	 * @return type
	 */
	public static function GetDataBySeasonCode($iParserId, $SeasonCode) {
		$DB = \classes\Core::init()->db;
		$sql = "SELECT ch.PARSER_URL, cp.PARSER_ID, cp.CHY_ID, chy.CH_ID, chy.YEAR, ch.NAME, c.ID as COUNTRY_ID, c.CODE as COUNTRY_CODE, cnp.CODE as COUNTRY_OUTER_CODE
FROM championship_parser cp
INNER JOIN championship_year chy ON chy.ID = cp.CHY_ID
INNER JOIN championship ch ON ch.ID = chy.CH_ID
INNER JOIN country c ON c.ID = ch.COUNTRY_ID
INNER JOIN country_parser cnp on cnp.COUNTRY_ID = c.ID
WHERE cp.PARSER_ID = '{$iParserId}' AND cp.CODE = '{$SeasonCode}' LIMIT 1";
		$rs = $DB->query($sql);
		return $DB->get_row_assoc($rs);
	}
	/**
	 * Получить все данные по чемпионату по $iParserId и коду чемпионата
	 * @param type $iParserId
	 * @param type $ChampCode 
	 * @return type
	 */
	public static function GetDataByChampCode($iParserId, $ChampCode, $iCountryId) {
		$DB = \classes\Core::init()->db;
		$sql = "SELECT ch.NAME, ch.OUTER_CODE, ch.PARSER_URL, c.ID as COUNTRY_ID, c.CODE as COUNTRY_CODE, cp.CODE as COUNTRY_OUTER_CODE  
FROM championship ch
INNER JOIN country c ON c.ID = ch.COUNTRY_ID
INNER JOIN country_parser cp on cp.COUNTRY_ID = c.ID
WHERE ch.PARSER_ID = '{$iParserId}' and ch.OUTER_CODE = '{$ChampCode}' and c.ID = '{$iCountryId}'";
		$rs = $DB->query($sql);
		$arResult = [];
		while ($item = $DB->get_row_assoc($rs)) {
			if (empty($arResult)) 
				$arResult = [
					'NAME'=>$item['NAME'],
					'OUTER_CODE'=>$item['OUTER_CODE'],
					'PARSER_URL'=>$item['PARSER_URL'],
					'COUNTRY_ID'=>$item['COUNTRY_ID'],
					'COUNTRY_CODE'=>$item['COUNTRY_CODE'],
					'COUNTRY_OUTER_CODE'=>[],
				];
			$arResult['COUNTRY_OUTER_CODE'][] = $item['COUNTRY_OUTER_CODE'];
		}
		return $arResult;
	}
}

