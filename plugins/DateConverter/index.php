<?php
// 언어 템플릿 *******************************************************************************************************
	// %Y = 4자리수 연도 [0000]
	// %y = 2자리수 연도 [00]
	
	// %M = 앞의 0을 표시하는 달. [01~12] or January/Febuary/March/April...
	// %m = 앞의 0을 표시하지 않는 달. [1~12] or Jan/Feb/Mar/Apr...
	
	// %D = 앞의 0을 표시하는 일(日). [01~31]
	// %d = 앞의 0을 표시하지 않는 일(日). [1~31]
	// %o = 서수 어미가 붙은 일(日). 주로 라틴 문자권에서 사용. [1~31] + [day ordinal postfix]
	
	// %H = 앞의 0을 표시하는 24시간. [00~24]
	// %h = 앞의 0을 표시하지 않는 24시간. [0~24]
	// %F = 앞의 0을 표시하는 12시간. [00~12]
	// %f = 앞의 0을 표시하지 않는 12시간. [0~12]
	
	// %I = 앞의 0을 표시하는 분. [00~59]
	// %i = 앞의 0을 표시하지 않는 분. [0~59]
	
	// %S = 앞의 0을 표시하는 초. [00~59]
	// %s = 앞의 0을 표시하지 않는 초. [0~59]

function convertDateFormat($argTarget, $argType) {
	global $pluginURL;
	
	$temp = explode('/', $pluginURL);
	array_shift($temp);
	array_shift($temp);
	
	include ROOT.'/'.implode('/', $temp)."/language.php";
	
	$rgDateFormat = array();
	$rgDateFormat['archive date'] = array("language" => "english", "format" => "%Y|%M");											// 저장소 날짜.
	$rgDateFormat['calendar head'] = array("language" => "english", "format" => "%m %Y");											// 달력 머릿날짜.
	$rgDateFormat['comment date'] = array("language" => "japanese", "format" => "%Y年 %M月{ %D日}{ %H時}{ %I分}");					// 댓글 날짜.
	$rgDateFormat['comment list date'] = array("language" => "korean", "format" => "%Y년 %M월{ %D일}");								// 댓글 목록 날짜.
	$rgDateFormat['guestbook comment date'] = array("language" => "korean", "format" => "%Y년 %M월{ %D일}{ %H시}{ %I분}");			// 방명록 날짜.
	$rgDateFormat['list date'] = array("language" => "korean", "format" => "%Y년 %M월{ %D일}");										// 목록 날짜.
	$rgDateFormat['notice date'] = array("language" => "korean", "format" => "%Y년 %M월{ %D일}{ %H시}{ %I분}{ %S초}");				// 공지 날짜.
	$rgDateFormat['post date'] = array("language" => "english", "format" => "{%M}{ %o,} %Y{ %H}{:%I}{:%S}");						// 포스트 날짜.
	$rgDateFormat['recent comment date'] = array("language" => "english", "format" => "{%M}{ %d,}{ %Y}{ %H}{:%I}{:%S}");			// 최근 댓글 날짜.
	$rgDateFormat['recent trackback date'] = array("language" => "english", "format" => "{%o }%M");									// 최근 트랙백 날짜.
	$rgDateFormat['trackback date'] = array("language" => "english", "format" => "%M{ %d,} %Y{ %H}{:%I}{:%S}");						// 트랙백 날짜.
	
	$strLanguage = $rgDateFormat[$argType]['language'];
	
	if (empty($strLanguage) || $strLanguage == "default") {
		$strLanguage = "korean";
	}
	
	eregi("^(([0-9]{4})/([0-9]{2})/?([0-9]{2})?)? ?(([0-9]{2}):([0-9]{2}):?([0-9]{2})?)?$", $argTarget, $rgTemp);
	$strYear = $rgTemp[2];
	$strMonth = $rgTemp[3];
	$strDay = $rgTemp[4];
	$strHour = $rgTemp[6];
	$strMinute = $rgTemp[7];
	$strSecond = $rgTemp[9];
	
	$rgCustomIdentifier = array();
	if ($strYear != false) {
		$rgCustomIdentifier['%Y'] = $strYear;
		$rgCustomIdentifier['%y'] = eregi_replace("^[0-9]{2}", "", $strYear);
	}
	if ($strMonth != false) {
		if (isset($rgDateInformation[$strLanguage]['month'][$strMonth])) {
			$rgCustomIdentifier['%M'] = $rgDateInformation[$strLanguage]['month'][$strMonth];
			if (isset($rgDateInformation[$strLanguage]['month']['length'])) {
				$rgCustomIdentifier['%m'] = eregi_replace("^(.{".$rgDateInformation[$strLanguage]['month']['length']."})(.*)$", "\\1", $rgDateInformation[$strLanguage]['month'][$strMonth]);
			} else {
				$rgCustomIdentifier['%m'] = $rgCustomIdentifier['%M'];
			}
		} else {
			$rgCustomIdentifier['%M'] = $strMonth;
			$rgCustomIdentifier['%m'] = eregi_replace("^[0-9]{2}", "", $strYear);
		}
	}
	if ($strDay != false) {
		$rgCustomIdentifier['%D'] = $strDay;
		$rgCustomIdentifier['%d'] = eregi_replace("^[0-9]{2}", "", $strYear);
		if (isset($rgDateInformation[$strLanguage]['day ordinal postfix']['00'])) {
			$rgCustomIdentifier['%o'] = $rgCustomIdentifier['%d'].$rgDateInformation[$strLanguage]['day ordinal postfix']['00'];
		} else if (isset($rgDateInformation[$strLanguage]['day ordinal postfix'][$strDay])) {
			$rgCustomIdentifier['%o'] = $rgCustomIdentifier['%d'].$rgDateInformation[$strLanguage]['day ordinal postfix'][$strDay];
		} else {
			$rgCustomIdentifier['%o'] = $rgCustomIdentifier['%d'];
		}
	}
	if ($strHour != false) {
		$rgCustomIdentifier['%H'] = $strHour;
		$rgCustomIdentifier['%h'] = eregi_replace("^0", "", $strHour);
		$rgCustomIdentifier['%f'] = ($rgCustomIdentifier['%h'] > 12) ? $rgCustomIdentifier['%h'] - 12 : $rgCustomIdentifier['%h'];
		$rgCustomIdentifier['%F'] = ($rgCustomIdentifier['%f'] < 10) ? '0'.$rgCustomIdentifier['%f'] : $rgCustomIdentifier['%f'];
	}
	if ($strMinute != false) {
		$rgCustomIdentifier['%I'] = $strMinute;
		$rgCustomIdentifier['%i'] = eregi_replace("^0", "", $strMinute);
	}
	if ($strSecond != false) {
		$rgCustomIdentifier['%S'] = $strSecond;
		$rgCustomIdentifier['%s'] = eregi_replace("^0", "", $strSecond);
	}
	
	$newTarget = strtr($rgDateFormat[$argType]['format'], $rgCustomIdentifier);
	$newTarget = eregi_replace("\{[^\{]*%[a-z][^\}]*\}", "", $newTarget);
	
	return trim(eregi_replace("\{|\}", "", $newTarget));
}

function removeHeadZero($number) {
	return eregi_replace("^0", "", $number);
}

function convertDateLangLD($argTarget, $argMother) {
	return convertDateFormat($argTarget, "list date");
}

function convertDateLangCLD($argTarget, $argMother) {
	return convertDateFormat($argTarget, "comment list date");
}

function convertDateLangND($argTarget, $argMother) {
	return convertDateFormat($argTarget, "notice date");
}

function convertDateLangPD($argTarget, $argMother) {
	return convertDateFormat($argTarget, "post date");
}

function convertDateLangTD($argTarget, $argMother) {
	return convertDateFormat($argTarget, "trackback date");
}

function convertDateLangCD($argTarget, $argMother) {
	return convertDateFormat($argTarget, "comment date");
}

function convertDateLangGCD($argTarget, $argMother) {
	return convertDateFormat($argTarget, "guestbook comment date");
}

function convertDateLangAD($argTarget, $argMother) {
	return convertDateFormat($argTarget, "archive date");
}

function convertDateLangCH($argTarget, $argMother) {
	return convertDateFormat($argTarget, "calendar head");
}

function convertDateLangRTD($argTarget, $argMother) {
	return convertDateFormat($argTarget, "recent trackback date");
}

function convertDateLangRCD($argTarget, $argMother) {
	return convertDateFormat($argTarget, "recent comment date");
}
?>