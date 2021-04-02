<?php

use Mpakfm\Printu;

include_once __DIR__ . '/../vendor/autoload.php';

define("EMAIL_ADMIN", "mpakfm@gmail.com");
//define("EMAIL", "bigbic@mail.ru");
define("EMAIL", "fsg79@yandex.ru");

$emailList = [
    'bigbic@mail.ru',
    'mpakfm@gmail.com',
];

Printu::obj(__DIR__)->title('__DIR__')->response('text');
Printu::setPath(__DIR__ . '/');

/**
 * Время когда начинается пауза
 */
define("PAUSE", false);
/**
 * Время когда начинается пауза
 */
define("P1", "22");
/**
 * Время когда пауза заканчивается
 */
define("P2", "6");
/**
 * Ограничитель. Собирать игры от NOW до этого ограничителя
 */
define("TSTOP", "12");

$dt = new DateTime();

Printu::obj('init')->dt()->response('text');
Printu::obj($dt->format("G"))->dt()->title('G')->response('text');

// Пауза

if (PAUSE && ($dt->format("G") > P1 || $dt->format("G") < P2) ) {
    die();
}

function request($url,$method='get',$data=array()) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE,  dirname(__FILE__).'/cookie.txt');
    if ($method=='post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    } else {
            curl_setopt($ch, CURLOPT_POST, 0);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: client.inplaysd.com',
    //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: test.mpakfm.ru',
'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0',
'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
'Accept-Language: ru,en;q=0.5',
'Pragma: no-cache'
));
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function mail_tpl($data) {
	$TPL = <<<HERE
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <title>{{title}}</title>
   <style type="text/css">  
    a {color: #4A72AF;}  
    body, #header h1, #header h2, p {margin: 0; padding: 0;}  
    #main {border: 1px solid #cfcece;}  
    img {display: block;}  
    #top-message p, #bottom-message p {color: #3f4042; font-size: 12px; font-family: Arial, Helvetica, sans-serif; }
    #header h1 {color: #ffffff !important; font-family: "Lucida Grande", "Lucida Sans", "Lucida Sans Unicode", sans-serif; font-size: 24px; margin-bottom: 0!important; padding-bottom: 0; }
    #header h2 {color: #ffffff !important; font-family: Arial, Helvetica, sans-serif; font-size: 24px; margin-bottom: 0 !important; padding-bottom: 0; }
    #header p {color: #ffffff !important; font-family: "Lucida Grande", "Lucida Sans", "Lucida Sans Unicode", sans-serif; font-size: 12px;  }
    h1, h2, h3, h4, h5, h6 {margin: 0 0 0.8em 0;}
    h3 {font-size: 28px; color: #444444 !important; font-family: Arial, Helvetica, sans-serif; }
    h4 {font-size: 22px; color: #4A72AF !important; font-family: Arial, Helvetica, sans-serif; }
    h5 {font-size: 18px; color: #444444 !important; font-family: Arial, Helvetica, sans-serif; }
    p {font-size: 12px; color: #444444 !important; font-family: "Lucida Grande", "Lucida Sans", "Lucida Sans Unicode", sans-serif; line-height: 1.5;}
   </style>
</head>
<body>  

<table width="100%" cellpadding="0" cellspacing="0" bgcolor="e4e4e4"><tr><td>
		<table id="main" width="600" align="center" cellpadding="0" cellspacing="15" bgcolor="ffffff">
			<tr>
				<td>
					<table id="header" cellpadding="10" cellspacing="0" align="center" bgcolor="8fb3e9">
						<tr>
							<td width="570" bgcolor="7aa7e9"><h1>{{h1}}</h1></td>
						</tr>
						<tr>
							<td width="570" align="right" bgcolor="7aa7e9"><p>{{date}}</p></td>
						</tr>
					</table><!-- header -->
				</td>
			</tr><!-- header -->
			<tr>
				<td></td>
			</tr>
			<tr>
				<td>
					<table id="content-1" cellpadding="2" cellspacing="3" align="center">
						<tr>
							<th>Время</th>
							<th>Лига</th>
							<th>Статус</th>
							<th>Игра</th>
						</tr>
						{{line}}
					</table><!-- content 1 -->
				</td>
			</tr><!-- content 1 -->
		</table><!-- main -->
	</td></tr></table><!-- wrapper -->

</body>  

</html>
HERE;
	$TPL = str_replace('{{title}}', $data['title'], $TPL);
	$TPL = str_replace('{{h1}}', $data['h1'], $TPL);
	$TPL = str_replace('{{date}}', $data['date'], $TPL);
	$arLines = array();
	foreach($data['lines'] as $line) {
		$arLines[] = '<tr>
	<td>'.$line['event_starttime'].'</td>
	<td>'.$line['league_name'].'</td>
	<td>'.$line['event_status'].'</td>
	<td>'.$line['home'].' - '.$line['away'].'</td>
</tr>';
	}
	$TPL = str_replace('{{line}}', implode("\n", $arLines), $TPL);
	return $TPL;
}

function getGames($page,$time) {
    global $dt;
    $matches = array();
    preg_match_all('/<tr class="new_match(.*)<\/tr>/Uis', $page,$matches);
    $cnt_games = count($matches[0]);
    Printu::log($cnt_games, '$cnt_games', 'file');
    if (!$cnt_games) return false;

    $gamePath = dirname(__FILE__).DIRECTORY_SEPARATOR.'games';

    if (!file_exists($gamePath)) {
        $arGames = [];
    } else {
        $arGames = explode("\n",file_get_contents($gamePath));
    }
    $arResult = array();
    $arResultId = array();
    foreach($matches[0] as $line) {
        $ar = array();
        $m = array();
        preg_match('/<td class="event_starttime">(.*)<\/td>/Uis', $line,$m);
        $arH = explode(":", strip_tags($m[1]));
        $iHour = (int)$arH[0];
        $iMin = (int)$arH[1];
        $STOP = TSTOP - 3;

        if ($time=='B') {
                if (($iHour==0 && $iMin==0) || $iHour > $STOP) {
                        continue;
                }
        } else {
                if ($iHour < $STOP && !($iHour==0 && $arH[1]==0)) {
                        continue;
                }
        }
        $iMoscowHour = $iHour + 3;
        if ($iMoscowHour >= 24) $iMoscowHour = $iMoscowHour - 24;
        $ar['event_starttime'] = ($iMoscowHour<10?'0':'').$iMoscowHour.':'.$arH[1];

        $m = array();
        preg_match('/<td style="display:none;">(.*)<\/td>/Uis', $line,$m);
        $ar['id'] = $m[1];
        $arResultId[] = $ar['id'];
        if (in_array($ar['id'], $arGames)) {
                continue;
        }
        $m = array();
        preg_match('/<td class="league_name">(.*)<\/td>/Uis', $line,$m);
        $ar['league_name'] = $m[1];
        $m = array();
        preg_match('/<td class="event_status"(.*)>(.*)<\/td>/Uis', $line,$m);
        $ar['event_status'] = $m[2];
        $m = array();
        preg_match('/<td class="event_player_name home"(.*)>(.*)<\/td>/Uis', $line,$m);
        $ar['home'] = strip_tags($m[2]);
        $m = array();
        preg_match('/<td class="event_player_name away"(.*)>(.*)<\/td>/Uis', $line,$m);
        $ar['away'] = strip_tags($m[2]);
        $arResult[] = $ar;
    }
    return array(
        'list'=>$arResult,
        'ids'=>$arResultId,
    );

    file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'games', implode("\n",$arResultId));
    $cnt = count($arResult);
    if (!$cnt) {
        die();
    }
    $tpl_data = array(
        'title'=>'INPLAY Новые игры ('.$cnt.')',
        'h1'=>'Новые игры на сайте INPLAY ('.$cnt.')',
        'date'=>$dt->format('d.m.Y H:i:s'),
        'lines'=>$arResult,
    );
    $body = mail_tpl($tpl_data);
    $headers  = "Content-type: text/html; charset=utf-8 \r\n";
    $res = mail(EMAIL,'Новые игры на INPLAY ('.$cnt.')', $body, $headers);
    return true;
}

function auth() {
    global $dt;
    $url = 'http://client.inplaysd.com/client/authentication/login';
    $data = request($url,'get',array());
    if (strlen($data) < 2000) {
        mail(EMAIL_ADMIN,"Ошибка на входе client.inplaysd.com ".$dt->format('H:i:s'), $data);
        Printu::log(substr($data,0,300),"Ошибка на входе client.inplaysd.com ".$dt->format('H:i:s'),'ajax');
        die;
    }

    $auth = array(
        'username'=>'r_bikeev',
        'password'=>'bikeE410',
        'submit_login_from'=>'Login'
    );

    $m = array();
    preg_match('/<form action="(.*)"/U', $data,$m);
    if (!isset($m[1]) || $m[1] == '') {
        mail(EMAIL_ADMIN,"Ошибка формы на входе client.inplaysd.com ".$dt->format('H:i:s'), $data);
        Printu::log(substr($data,0,300),"Ошибка формы на входе client.inplaysd.com ".$dt->format('H:i:s'),'ajax');
        die;
    }
    $action = $m[1];

    unset($data);

    $page = request($action,'post',$auth);
    if (strlen($page) < 2000) {
        mail(EMAIL_ADMIN,"Ошибка авторизации client.inplaysd.com ".$dt->format('H:i:s'), $page);
        Printu::log(substr($page,0,300),"Ошибка авторизации client.inplaysd.com ".$dt->format('H:i:s'),'ajax');
        die;
    }
    return $page;
}

function send_mail($arResult) {
    global $dt, $emailList;
    $cnt = count($arResult);
    Printu::obj($cnt)->title('$cnt')->response('text');
    if (!$cnt) {
        die();
    }
    $tpl_data = array(
        'title'=>'INPLAY '.$dt->format('H:i:s').' Новые игры ('.$cnt.')',
        'h1'=>'Новые игры на сайте INPLAY ('.$cnt.')',
        'date'=>$dt->format('d.m.Y H:i:s'),
        'lines'=>$arResult,
    );
    $body = mail_tpl($tpl_data);
    $headers  = "Content-type: text/html; charset=utf-8 \r\n";
    $headers .= "From: Parser INPLAY <noreply@mpakfmtest.ru>\r\n";
    $to = implode(', ', $emailList);
    //$subject = 'Новые игры на inplaysd.com ' . '('.$cnt.')';
    //$subject = $tpl_data['title'];
    Printu::obj($tpl_data['title'])->title('title')->response('text');
    $subject = "=?utf-8?B?" . base64_encode($tpl_data['title']) . "?=";
    $res = mail($to, $subject, $body, $headers);
    Printu::obj($res)->title('send_mail $subject: "'.$subject.'", to: ' . $to)->response('text');
    return true;
}

Printu::obj('parser')->dt()->file('cron');

// Ночь - Утро
if ($dt->format("G") < TSTOP) {
    Printu::obj('Ночь - Утро')->dt()->file('cron');
    $url = 'http://client.inplaysd.com/client/calendar_football/today';
    $data = request($url,'get',array());
    $arResult = getGames($data,"B");
    Printu::obj($arResult)->file('cron');
    if ($arResult===false) {
        $data = auth();
        $arResult = getGames($data,"B");
    }
    file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'games', implode("\n",$arResult['ids']));
    send_mail($arResult['list']);
} 
// День / Следющий Ночь - Утро
else {
    Printu::obj('День / Следющий Ночь - Утро')->dt()->file('cron');
    // today > 12
    $url = 'http://client.inplaysd.com/client/calendar_football/today';
    $data = request($url,'get',array());
    $arResult = getGames($data,"A");
    if ($arResult===false) {
        $data = auth();
        $arResult = getGames($data,"A");
    }
    Printu::obj($arResult)->file('cron');

    // tomorrow < 12
    $dt1 = new DateTime("now",new DateTimeZone("UTC"));
    $dt1->modify('+1 day');
    $next_day = date_create_from_format("d.m.Y H:i:s", $dt1->format("d").'.'.$dt1->format("m").'.'.$dt1->format("Y").' 00:00:00',new DateTimeZone("UTC"));
    $nex_day_url = "http://client.inplaysd.com/client/calendar_football/schedule/day/".$next_day->format('U');
    $data = request($nex_day_url,'get',array());
    $arResult2 = getGames($data,"B");
    $arResult['ids'] = array_merge($arResult['ids'],$arResult2['ids']);
    $arResult['list'] = array_merge($arResult['list'],$arResult2['list']);
    file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'games', implode("\n",$arResult['ids']));
    send_mail($arResult['list']);
}
