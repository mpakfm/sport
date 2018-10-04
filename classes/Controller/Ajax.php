<?php

namespace classes\Controller;

class Ajax extends \classes\Controller {
	
	public function actionIndex() {
		
	}
	public function actionTeam() {
		header("Content-type:application/json");
		$arResponse = [
			'msg'=>false,
			'result'=>json_encode([]),
			'error'=>false,
			'error_msg'=>''
		];
		$sAction = $_POST['action'];
		try {
			switch ($sAction) {
				case"delete":
					$team_id = (int)$_POST['team_id'];
					if (!$team_id) {
						$arResponse['error'] = true;
						$arResponse['error_msg'] = "Команда не найдена";
						break;
					}
					$oTeam = \classes\Models\Team::GetById($team_id);
					$oTeam->Delete();
					$arResponse['msg'] = "Удалено";
				break;
			}
		} catch (\classes\BaseException $ex) {
			$arResponse['error'] = true;
			$arResponse['error_msg'] = $ex->getMsg();
		}
		
		$this->setRenderTpl('json/parser.twig');
		$this->addTplParams('response',$arResponse);
	}
	
	public function actionAnalizer() {
		header("Content-type:application/json");
		$arResponse = [
			'msg'=>false,
			'result'=>json_encode([]),
			'error'=>false,
			'error_msg'=>''
		];
		$sAction = $_POST['action'];
		try {
			switch ($sAction) {
				case"select_teams":
					$champ_id = (int)$_POST['champ'];
					$cmd1 = (int)$_POST['cmd1'];
					$cmd2 = (int)$_POST['cmd2'];
					if (isset($_POST['exclude']) && is_array($_POST['exclude']))
						$exclude = $_POST['exclude'];
					else 
						$exclude = [];
					if (!$champ_id || !$cmd1 || !$cmd2) {
						$arResponse['error'] = true;
						$arResponse['error_msg'] = "Команды не найдены";
						break;
					}
					$oChamp = \classes\Models\Championship::GetById($champ_id);
					$oTeam1 = \classes\Models\Team::GetById($cmd1);
					$oTeam2 = \classes\Models\Team::GetById($cmd2);
					$arResponse['result']= [
						'team1' =>$oTeam1->ID,
						'team2' =>$oTeam2->ID
					];
					$arPair = \classes\Models\GameTeam::GetPair($oTeam1->ID,$oTeam2->ID,$exclude);
					$arResponse['result']['pair'] = $arPair;
					$arResponse['msg'] = "Ok";
				break;
				case"search_teams":
					$champ_id = (int)$_POST['champ'];
					$cmd1 = trim($_POST['cmd1']);
					$cmd2 = trim($_POST['cmd2']);
					if (!$champ_id || $cmd1 == '' || $cmd2 == '') {
						$arResponse['error'] = true;
						$arResponse['error_msg'] = "Чемпионат не найден";
						break;
					}
					$oChamp = \classes\Models\Championship::GetById($champ_id);
					$arTeam1 = \classes\Models\Team::GetByName($cmd1, $oChamp->COUNTRY_ID);
					$arTeam2 = \classes\Models\Team::GetByName($cmd2, $oChamp->COUNTRY_ID);
					$arResponse['result']= [
						'team1' =>$arTeam1,
						'team2' =>$arTeam2
					];
					$arResponse['msg'] = "Ok";
				break;
			}
		} catch (\classes\BaseException $ex) {
			$arResponse['error'] = true;
			$arResponse['error_msg'] = $ex->getMsg();
		}
		
		$this->setRenderTpl('json/parser.twig');
		$this->addTplParams('response',$arResponse);
	}
	
	public function actionParser() {
		header("Content-type:application/json");
		$arResponse = [
			'msg'=>false,
			'result'=>json_encode([]),
			'error'=>false,
			'error_msg'=>''
		];
		try {
			$arAccessAcion = ['cmd','game','season','save_cmd'];
			$iParserId = (int)$_POST['parser'];
			$sAction = $_POST['action'];
			
			if (!$iParserId || !in_array($sAction, $arAccessAcion)) {
				throw new \classes\BaseException('PARAMS_ERROR');
			}
			
			$oParser = new \classes\Lib\Parser();
			switch ($sAction) {
				case"game":
					// Championship code
					$sCode = trim(str_replace("'","\\'",$_POST['code']));
					if ($sCode == '') {
						throw new \classes\BaseException('PARAMS_ERROR');
					}
					$data = \classes\Models\Parser::GetDataBySeasonCode($iParserId,$sCode);
					if (!$data) {
						throw new \classes\BaseException('PARAMS_ERROR');
					}
					$arResult = $oParser->grabber_game($data,$sCode);
					$arResponse['msg'] = 'Проверено: '.$arResult['all']."; Записано: ".$arResult['cnt'];
					$arResponse['result'] = $arResult['games'];
				break;
				case"cmd":
					// Championship code
					$sCode = trim(str_replace("'","\\'",$_POST['code']));
					if ($sCode == '') {
						throw new \classes\BaseException('PARAMS_ERROR');
					}
					$data = \classes\Models\Parser::GetDataBySeasonCode($iParserId,$sCode);
					if (!$data) {
						throw new \classes\BaseException('PARAMS_ERROR');
					}
					$arResult = $oParser->grabber_cmd($data,$sCode);
					$arResponse['msg'] = 'Проверено: '.count($arResult['code']);
					$arResponse['result'] = $arResult;
				break;
				case"season":
					// Championship code
					$sCode = trim(str_replace("'","\\'",$_POST['code']));
					$iCountryId = (int)$_POST['country'];
					if ($sCode == '' || !$iCountryId) {
						throw new \classes\BaseException('PARAMS_ERROR');
					}
					$data = \classes\Models\Parser::GetDataByChampCode($iParserId,$sCode,$iCountryId);
					if (!$data) {
						throw new \classes\BaseException('PARAMS_ERROR');
					}
					$arRs = $oParser->grabber_seasons($data);
					
					$arResponse['msg'] = 'Проверено: '.$arRs['all'].'; Вставлено: '.$arRs['insert'].'; Обновлено: '.$arRs['update'];
				break;
				case"save_cmd":
					// Save cmd list for ChampionshipYear
					$iChyId = (int)$_POST['chy_id'];
					$iParserId = (int)$_POST['parser'];
					$rsTeams = \classes\Models\ChampionshipTeam::GetList(['CHY_ID'=>['table'=>'cht','type'=>'=','value'=>$iChyId],'PARSER_ID'=>['table'=>'tp','type'=>'=','value'=>$iParserId]]);
					$DB = \classes\Core::init()->db;
					$arChmpTeams = [];
					while ($item = $DB->get_row_assoc($rsTeams)) {
						$arChmpTeams[$item['TP_CODE']] = [
							'ID'=>$item['ID'],
							'CHY_ID'=>$item['CHY_ID'],
							'TEAM_ID'=>$item['TEAM_ID'],
							'NUMBER'=>$item['NUMBER'],
							'TP_CODE'=>$item['TP_CODE'],
							'UPDATED'=>false,
							'INSERT'=>false,
							'DELETE'=>true,
						];
					}
					$arResponse['ins'] = 0;
					$arResponse['upd'] = 0;
					$arResponse['del'] = 0;
					$rsChmYear = \classes\Models\ChampionshipYear::GetList(['ID'=>['table'=>'chy','type'=>'=','value'=>$iChyId]]);
					$arChYear = $DB->get_row_assoc($rsChmYear);
					foreach ($_POST['save_list'] as $prs_key => $prs_cmd) {
						if (isset($arChmpTeams[$prs_key])) {
							if ($prs_cmd['new_name'] && trim($prs_cmd['new_name']) != '') {
								$arChmpTeams[$prs_key]['DELETE'] = true;
								$oTeam = new \classes\Models\Team();
								$oTeam->COUNTRY_ID = $arChYear['COUNTRY_ID'];
								$oTeam->NAME = trim($prs_cmd['new_name']);
								$oTeam->Insert();
								$oChTeam = new \classes\Models\ChampionshipTeam();
								$oChTeam->CHY_ID = $arChYear['ID'];
								$oChTeam->TEAM_ID = $oTeam->ID;
								$oChTeam->NUMBER = $prs_cmd['number'];
								$oChTeam->Insert();
								\classes\Models\Team::UpdateParserCode($iChyId, $oTeam->ID, $iParserId, $prs_key);
								$arResponse['ins']++;
							}
							else {
								if ($arChmpTeams[$prs_key]['TEAM_ID'] != $prs_cmd['cmd_id']) {
									$arChmpTeams[$prs_key]['TEAM_ID'] = $prs_cmd['cmd_id'];
									$arChmpTeams[$prs_key]['UPDATED'] = true;
									$arChmpTeams[$prs_key]['DELETE'] = false;
								}
								if ($arChmpTeams[$prs_key]['NUMBER'] != $prs_cmd['number']) {
									$arChmpTeams[$prs_key]['NUMBER'] = $prs_cmd['number'];
									$arChmpTeams[$prs_key]['UPDATED'] = true;
									$arChmpTeams[$prs_key]['DELETE'] = false;
								}
								$arChmpTeams[$prs_key]['DELETE'] = false;
							}
						} else {
							if ($prs_cmd['new_name'] && trim($prs_cmd['new_name']) != '') {
								$oTeam = new \classes\Models\Team();
								$oTeam->COUNTRY_ID = $arChYear['COUNTRY_ID'];
								$oTeam->NAME = trim($prs_cmd['new_name']);
								$oTeam->Insert();
								$prs_cmd['cmd_id'] = $oTeam->ID;
							}
							$oChTeam = new \classes\Models\ChampionshipTeam();
							$oChTeam->CHY_ID = $arChYear['ID'];
							$oChTeam->TEAM_ID = $prs_cmd['cmd_id'];
							$oChTeam->NUMBER = $prs_cmd['number'];
							$oChTeam->Insert();
							\classes\Models\Team::UpdateParserCode($iChyId, $prs_cmd['cmd_id'], $iParserId, $prs_key);
							$arResponse['ins']++;
						}
					}

					foreach ($arChmpTeams as $prs_key => $item) {
						if ($item['UPDATED']) {
							$oItem = \classes\Models\ChampionshipTeam::GetById($item['ID']);
							if ($oItem->TEAM_ID != $item['TEAM_ID']) {
								\classes\Models\Team::DeleteParserCode($iChyId, $oItem->TEAM_ID, $iParserId);
								\classes\Models\Team::UpdateParserCode($iChyId, $item['TEAM_ID'], $iParserId, $item['TP_CODE']);
								$oItem->TEAM_ID = $item['TEAM_ID'];
							}
							$oItem->NUMBER = $item['NUMBER'];
							$oItem->Update();
							
							$arResponse['upd']++;
						}
						elseif ($item['DELETE']) {
							\classes\Models\Team::DeleteParserCode($iChyId, $item['TEAM_ID'], $iParserId);
							$oItem = \classes\Models\ChampionshipTeam::GetById($item['ID']);
							$oItem->Delete();
							$arResponse['del']++;
						}
					}
					$arResponse['msg'] = 'Вставлено: '.$arResponse['ins'].'; Обновлено: '.$arResponse['upd'].'; Удалено: '.$arResponse['del'];
				break;
			}
			
			
		} catch (\classes\BaseException $ex) {
			$arResponse['error'] = true;
			$arResponse['error_msg'] = $ex->getMsg();
		}
		$this->setRenderTpl('json/parser.twig');
		$this->addTplParams('response',$arResponse);
	}
}