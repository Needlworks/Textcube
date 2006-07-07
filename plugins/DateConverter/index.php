<?php
function convertDateFormat($argTarget, $argType) {
	/*
		$rgDateFormat['archive date'] = array()
			① string	- 언어 : default/english/korean.
			② integer	- 달(month)의 문자열 길이. '0'이면 문자열을 자르지 않고 전부 보여준다.
			③ integer	- 요일(day)의 문자열 길ㅇ. '0'이면 문자열을 자르지 않고 전부 보여준다.
			④ boolean	- 숫자에 자리수를 맞출 것인지 여부. 맞추면 true, 안 맞추면 false. 예) 06월, 09일.
	*/
	$rgDateFormat = array();
	$rgDateFormat['archive date'] = array("language" => "english", "format" => "%M %Y");											// 저장소 날짜.
	$rgDateFormat['calendar head'] = array("language" => "english", "format" => "%m %Y");											// 달력 머릿날짜.
	$rgDateFormat['comment date'] = array("language" => "japanese", "format" => "%Y年 %M月{ %D日}{ %H時}{ %I分}");					// 댓글 날짜.
	$rgDateFormat['comment list date'] = array("language" => "korean", "format" => "%Y년 %M월{ %D일}");								// 댓글 목록 날짜.
	$rgDateFormat['guestbook comment date'] = array("language" => "korean", "format" => "%Y년 %M월{ %D일}{ %H시}{ %I분}");			// 방명록 날짜.
	$rgDateFormat['list date'] = array("language" => "korean", "format" => "%Y년 %M월{ %D일}");										// 목록 날짜.
	$rgDateFormat['notice date'] = array("language" => "korean", "format" => "%Y년 %M월{ %D일}{ %H시}{ %I분}{ %S초}");				// 공지 날짜.
	$rgDateFormat['post date'] = array("language" => "english", "format" => "{%o }%M %Y{ %H}{:%I}{:%S}");							// 포스트 날짜.
	$rgDateFormat['trackback date'] = array("language" => "english", "format" => "%M{ %d,} %Y{ %H}{:%I}{:%S}");						// 트랙백 날짜.
	
	// 언어 템플릿 *******************************************************************************************************
	// %Y = [0000]
	// %M = [01~12] or January/Febuary/March/April...
	// %D = [01~31]
	// %y = [00]
	// %m = [1~12] or Jan/Feb/Mar/Apr...
	// %d = [1~31]
	// %o = [1~31] + [day ordinal postfix]
	// %H = [00~24]
	// %I = [00~59]
	// %S = [00~59]
	// %F = [00~12]
	// %h = [0~24]
	// %i = [0~59]
	// %s = [0~59]
	// %f = [0~12]
	$rgDateInformation = array();
	
	// - 영어.
	$rgDateInformation['english']['day ordinal postfix'] = array(); // 날짜 뒤에 붙는 문자. 
	$rgDateInformation['english']['day ordinal postfix']['00'] = NULL;
	$rgDateInformation['english']['day ordinal postfix']['01'] = 'st';
	$rgDateInformation['english']['day ordinal postfix']['02'] = 'nd';
	$rgDateInformation['english']['day ordinal postfix']['03'] = 'rd';
	$rgDateInformation['english']['day ordinal postfix']['04'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['05'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['06'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['07'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['08'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['09'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['10'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['11'] = 'st';
	$rgDateInformation['english']['day ordinal postfix']['12'] = 'nd';
	$rgDateInformation['english']['day ordinal postfix']['13'] = 'rd';
	$rgDateInformation['english']['day ordinal postfix']['14'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['15'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['16'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['17'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['18'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['19'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['20'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['21'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['22'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['23'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['24'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['25'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['26'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['27'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['28'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['29'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['30'] = 'th';
	$rgDateInformation['english']['day ordinal postfix']['31'] = 'st';

	//$rgDateInformation['english']['month']['01'] = 'January';
	//$rgDateInformation['english']['month']['02'] = 'Febuary';
	//$rgDateInformation['english']['month']['03'] = 'March';
	//$rgDateInformation['english']['month']['04'] = 'April';
	//$rgDateInformation['english']['month']['05'] = 'May';
	//$rgDateInformation['english']['month']['06'] = 'June';
	//$rgDateInformation['english']['month']['07'] = 'July';
	//$rgDateInformation['english']['month']['08'] = 'August';
	//$rgDateInformation['english']['month']['09'] = 'September';
	//$rgDateInformation['english']['month']['10'] = 'October';
	//$rgDateInformation['english']['month']['11'] = 'November';
	//$rgDateInformation['english']['month']['12'] = 'December';

	//$rgDateInformation['english']['weekday'][0] = 'Sunday';
	//$rgDateInformation['english']['weekday'][1] = 'Monday';
	//$rgDateInformation['english']['weekday'][2] = 'Tuesday';
	//$rgDateInformation['english']['weekday'][3] = 'Wednesday';
	//$rgDateInformation['english']['weekday'][4] = 'Thursday';
	//$rgDateInformation['english']['weekday'][5] = 'Friday';
	//$rgDateInformation['english']['weekday'][6] = 'Saturday';
	
	
	$strLanguage = $rgDateFormat[$argType]['language'];
	
	if (!empty($strLanguage) && $strLanguage != "default") {
		list($strDateHead, $strDateTail) = split("( )+", $argTarget);
		list($strYear, $strMonth, $strDay) = explode("/", $strDateHead);
		if (!empty($strDateTail)) {
			list($strHour, $strMinute, $strSecond) = explode("/", $strDateTail);
		}
		
		$rgTimeFormat = split("(/|:| )", $argTarget);
		
		setlocale(LC_TIME, $strLanguage);
		$strFlag = strftime("%p", strtotime($argTarget));
		
		// PHP 자체 내에 설정이 들어있는 언어인 경우.
		if ($strLanguage == "english" || ($strFlag != "PM" && $strFlag != "AM")) {
			$rgCustomIdentifier = array();
			$rgCustomIdentifier['%Y'] = strftime("%Y", strtotime($argTarget));
			if (eregi("^[0-9]{1,2}$", strftime("%b", strtotime($argTarget)), $rgTemp)) {
				$rgCustomIdentifier['%M'] = strftime("%m", strtotime($argTarget));
			} else {
				$rgCustomIdentifier['%M'] = strftime("%B", strtotime($argTarget));
			}
			if (isset($rgTimeFormat[2]))
				$rgCustomIdentifier['%D'] = strftime("%d", strtotime($argTarget));
			
			$rgCustomIdentifier['%y'] = strftime("%y", strtotime($argTarget));
			$rgCustomIdentifier['%m'] = strftime("%b", strtotime($argTarget));
			if (isset($rgTimeFormat[2]))
				$rgCustomIdentifier['%d'] = intval(strftime("%d", strtotime($argTarget)));
			
			if (isset($rgTimeFormat[2])) {
				if (isset($rgDateInformation[$strLanguage]['day ordinal postfix'][$rgCustomIdentifier['%D']])) {
					$rgCustomIdentifier['%o'] = $rgCustomIdentifier['%d'].$rgDateInformation[$strLanguage]['day ordinal postfix'][$rgCustomIdentifier['%D']];
				} else {
					$rgCustomIdentifier['%o'] = $rgCustomIdentifier['%d'];
				}
			}
			
			if (isset($rgTimeFormat[3]))
				$rgCustomIdentifier['%H'] = strftime("%H", strtotime($argTarget));
			if (isset($rgTimeFormat[4]))
				$rgCustomIdentifier['%I'] = strftime("%M", strtotime($argTarget));
			if (isset($rgTimeFormat[5]))
				$rgCustomIdentifier['%S'] = strftime("%S", strtotime($argTarget));
			
			if (isset($rgTimeFormat[3]))
				$rgCustomIdentifier['%h'] = strftime("%I", strtotime($argTarget));
			if (isset($rgTimeFormat[4]))
				$rgCustomIdentifier['%i'] = intval(strftime("%M", strtotime($argTarget)));
			if (isset($rgTimeFormat[5]))
				$rgCustomIdentifier['%s'] = intval(strftime("%S", strtotime($argTarget)));
			
			if (isset($rgTimeFormat[3]))
				$rgCustomIdentifier['%F'] = intval(strftime("%H", strtotime($argTarget)));
			if (isset($rgTimeFormat[3]))
				$rgCustomIdentifier['%f'] = intval(strftime("%I", strtotime($argTarget)));
			
			$newTarget = strtr($rgDateFormat[$argType]['format'], $rgCustomIdentifier);
			$newTarget = eregi_replace("\{[^\{]*%[a-z][^\}]*\}", "", $newTarget);
			$newTarget = eregi_replace("\{|\}", "", $newTarget);
		// PHP에 설정이 들어있지 않은 언어의 경우, 자체 설정을 사용.
		} else {
			$strMonth = strftime("%m", strtotime($argTarget));
			$strday = strftime("%d", strtotime($argTarget));
			
			$rgCustomIdentifier = array();
			$rgCustomIdentifier['%Y'] = strftime("%Y", strtotime($argTarget));
			$rgCustomIdentifier['%M'] = $rgDateInformation[$strLanguage]['month'][$strMonth];
			if (isset($rgTimeFormat[2]))
				$rgCustomIdentifier['%D'] = strftime("%d", strtotime($argTarget));
			$rgCustomIdentifier['%y'] = strftime("%y", strtotime($argTarget));
			$rgCustomIdentifier['%m'] = UTF8::lessen($rgDateInformation[$strLanguage]['month'][$strMonth], 3);
			if (isset($rgTimeFormat[2]))
				$rgCustomIdentifier['%d'] = intval(strftime("%d", strtotime($argTarget)));
			
			if (isset($rgTimeFormat[2])) {
				if (isset($rgDateInformation[$strLanguage]['day ordinal postfix'][$rgCustomIdentifier['%D']])) {
					$rgCustomIdentifier['%o'] = $rgCustomIdentifier['%d'].$rgDateInformation[$strLanguage]['day ordinal postfix'][$rgCustomIdentifier['%D']];
				} else {
					$rgCustomIdentifier['%o'] = $rgCustomIdentifier['%d'];
				}
			}
						
			if (isset($rgTimeFormat[3]))
				$rgCustomIdentifier['%H'] = strftime("%H", strtotime($argTarget));
			if (isset($rgTimeFormat[4]))
				$rgCustomIdentifier['%I'] = strftime("%M", strtotime($argTarget));
			if (isset($rgTimeFormat[5]))
				$rgCustomIdentifier['%S'] = strftime("%S", strtotime($argTarget));
			
			if (isset($rgTimeFormat[3]))
				$rgCustomIdentifier['%h'] = strftime("%I", strtotime($argTarget));
			if (isset($rgTimeFormat[4]))
				$rgCustomIdentifier['%i'] = intval(strftime("%M", strtotime($argTarget)));
			if (isset($rgTimeFormat[5]))
				$rgCustomIdentifier['%s'] = intval(strftime("%S", strtotime($argTarget)));
			
			if (isset($rgTimeFormat[3]))
				$rgCustomIdentifier['%F'] = intval(strftime("%H", strtotime($argTarget)));
			if (isset($rgTimeFormat[3]))
				$rgCustomIdentifier['%f'] = intval(strftime("%I", strtotime($argTarget)));
			
			$newTarget = strtr($rgDateFormat[$argType]['format'], $rgCustomIdentifier);
			$newTarget = eregi_replace("\{[^\{]*%[a-z][^\}]*\}", "", $newTarget);
			$newTarget = eregi_replace("\{|\}", "", $newTarget);
		}
	} else {
		$newTarget = $argTarget;
	}
	
	return $newTarget;
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
?>