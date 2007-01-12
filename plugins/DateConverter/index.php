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
	
	// %R = AM/PM(대문자) 시간 항목이 출력될 때만 활성화 됨.
	// %r = am/pm(소문자) 시간 항목이 출력될 때만 활성화 됨.
	
	// %S = 앞의 0을 표시하는 초. [00~59]
	// %s = 앞의 0을 표시하지 않는 초. [0~59]

include_once "language.php";
	
function convertDateFormat($argTarget, $argType) {
	global $pluginURL, $configVal, $rgDateInformation;
	
	$temp = explode('/', $pluginURL);
	array_shift($temp);
	array_shift($temp);
	
	if (empty($configVal)) {
		include 'config.ini.php';
		$tempArray = $data;
		unset($data);
		
	} else {
		requireComponent('Tattertools.Function.misc');
		$tempArray = misc::fetchConfigVal($configVal);
	}
	
	$rgDateFormat = array();
	$rgDateFormat['archive date'] = array("language" => $tempArray['language'], "format" => $tempArray['archive_date']);
	$rgDateFormat['calendar head'] = array("language" => $tempArray['language'], "format" => $tempArray['calendar_date']);
	$rgDateFormat['comment date'] = array("language" => $tempArray['language'], "format" => $tempArray['comment_date']);
	$rgDateFormat['comment list date'] = array("language" => $tempArray['language'], "format" => $tempArray['comment_list_date']);
	$rgDateFormat['guestbook date'] = array("language" => $tempArray['language'], "format" => $tempArray['guestbook_date']);
	$rgDateFormat['list date'] = array("language" => $tempArray['language'], "format" => $tempArray['list_date']);
	$rgDateFormat['notice date'] = array("language" => $tempArray['language'], "format" => $tempArray['notice_date']);
	$rgDateFormat['post date'] = array("language" => $tempArray['language'], "format" => $tempArray['post_date']);
	$rgDateFormat['recent comment date'] = array("language" => $tempArray['language'], "format" => $tempArray['recent_comment_date']);
	$rgDateFormat['recent trackback date'] = array("language" => $tempArray['language'], "format" => $tempArray['recent_trackback_date']);
	$rgDateFormat['trackback date'] = array("language" => $tempArray['language'], "format" => $tempArray['trackback_date']);
	
	if (isset($rgDateFormat[$argType]))
		$strLanguage = $rgDateFormat[$argType]['language'];
	else
		return $argTarget;
	
	if (empty($strLanguage) || $strLanguage == "default") {
		$strLanguage = "korean";
	}
	
	$strYear = NULL;
	$strMonth = NULL;
	$strDay = NULL;
	$strHour = NULL;
	$strMinute = NULL;
	$strSecond = NULL;
	
	switch ($argType) {
		case 'archive date':
		case 'calendar head':
			if (preg_match('@^([0-9]{4})/([0-9]{2})$@', $argTarget, $rgTemp)) {
				$strYear = $rgTemp[1];
				$strMonth = $rgTemp[2];
			} else if (preg_match('@^([0-9]{4})/([0-9]{2})/([0-9]{2})$@', $argTarget, $rgTemp)) {
				$strYear = $rgTemp[1];
				$strMonth = $rgTemp[2];
				$strDay = $rgTemp[3];
			}
			break;
		case 'comment list date':
		case 'list date':
			if (preg_match('@^([0-9]{4})/([0-9]{2}/([0-9]{2})$@', $argTarget, $rgTemp)) {
				$strYear = $rgTemp[1];
				$strMonth = $rgTemp[2];
				$strDay = $rgTemp[3];
			} else if (preg_match('@^([0-9]{2}):([0-9]{2}):([0-9]{2})$@', $argTarget, $rgTemp)) {
				$strHour = $rgTemp[1];
				$strMinute = $rgTemp[2];
				$strSecond = $rgTemp[3];
			}
			break;
		case 'comment date':
		case 'guestbook date':
		case 'notice date':
		case 'post date':
		case 'trackback date':
			preg_match('@^([0-9]{4})/([0-9]{2})/([0-9]{2}) ([0-9]{2}):([0-9]{2})$@', $argTarget, $rgTemp);
			$strYear = $rgTemp[1];
			$strMonth = $rgTemp[2];
			$strDay = $rgTemp[3];
			$strHour = $rgTemp[4];
			$strMinute = $rgTemp[5];
			break;
		case 'recent comment date':
		case 'recent trackback date':
			if (preg_match('@^([0-9]{2})/([0-9]{2})$@', $argTarget, $rgTemp)) {
				$strMonth = $rgTemp[1];
				$strDay = $rgTemp[2];
			} else if (preg_match('@^([0-9]{2}):([0-9]{2})$@', $argTarget, $rgTemp)) {
				$strHour = $rgTemp[1];
				$strMinute = $rgTemp[2];
			} else if (preg_match('@^([0-9]{4})$@', $argTarget, $rgTemp)) {
				$strYear = $rgTemp[1];
			}
			break;
	}
	
	$rgCustomIdentifier = array();
	if (!is_null($strYear)) {
		$rgCustomIdentifier['%Y'] = $strYear;
		$rgCustomIdentifier['%y'] = removeHeadZero($strYear);
	}
	if (!is_null($strMonth)) {
		if (isset($rgDateInformation[$strLanguage]['month'][$strMonth])) {
			$rgCustomIdentifier['%M'] = $rgDateInformation[$strLanguage]['month'][$strMonth];
			if (isset($rgDateInformation[$strLanguage]['month']['length'])) {
				$rgCustomIdentifier['%m'] = preg_replace('@^(.{'.$rgDateInformation[$strLanguage]['month']['length'].'})(.*)$@', '\1', $rgDateInformation[$strLanguage]['month'][$strMonth]);
			} else {
				$rgCustomIdentifier['%m'] = $rgCustomIdentifier['%M'];
			}
		} else {
			$rgCustomIdentifier['%M'] = $strMonth;
			$rgCustomIdentifier['%m'] = removeHeadZero($strMonth);
		}
	}
	if (!is_null($strDay)) {
		$rgCustomIdentifier['%D'] = $strDay;
		$rgCustomIdentifier['%d'] = removeHeadZero($strDay);
		if (isset($rgDateInformation[$strLanguage]['day ordinal postfix']['00'])) {
			$rgCustomIdentifier['%o'] = $rgCustomIdentifier['%d'].$rgDateInformation[$strLanguage]['day ordinal postfix']['00'];
		} else if (isset($rgDateInformation[$strLanguage]['day ordinal postfix'][$strDay])) {
			$rgCustomIdentifier['%o'] = $rgCustomIdentifier['%d'].$rgDateInformation[$strLanguage]['day ordinal postfix'][$strDay];
		} else {
			$rgCustomIdentifier['%o'] = $rgCustomIdentifier['%d'];
		}
	}
	if (!is_null($strHour)) {
		$rgCustomIdentifier['%H'] = $strHour;
		$rgCustomIdentifier['%h'] = removeHeadZero($strHour);
		$rgCustomIdentifier['%f'] = ($rgCustomIdentifier['%h'] > 12) ? $rgCustomIdentifier['%h'] - 12 : $rgCustomIdentifier['%h'];
		$rgCustomIdentifier['%F'] = ($rgCustomIdentifier['%f'] < 10) ? '0'.$rgCustomIdentifier['%f'] : $rgCustomIdentifier['%f'];
		$rgCustomIdentifier['%r'] = ($rgCustomIdentifier['%h'] >= 12) ? 'pm' : 'am';
		$rgCustomIdentifier['%R'] = strtoupper($rgCustomIdentifier['%r']);
	}
	if (!is_null($strMinute)) {
		$rgCustomIdentifier['%I'] = $strMinute;
		$rgCustomIdentifier['%i'] = removeHeadZero($strMinute);
	}
	if (!is_null($strSecond)) {
		$rgCustomIdentifier['%S'] = $strSecond;
		$rgCustomIdentifier['%s'] = removeHeadZero($strSecond);
	}
	
	$newTarget = strtr($rgDateFormat[$argType]['format'], $rgCustomIdentifier);
	$newTarget = preg_replace("/\{[^\{]*%[YyMmDdoHhFfRrIiSs][^\}]*\}/", '', $newTarget);
	
	return trim(preg_replace('@\{|\}@', '', $newTarget));
}

function removeHeadZero($number) {
	return preg_replace('@^0@', '', $number);
}

function convertDateLangLD($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'list date');
}

function convertDateLangCLD($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'comment list date');
}

function convertDateLangND($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'notice date');
}

function convertDateLangPD($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'post date');
}

function convertDateLangTD($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'trackback date');
}

function convertDateLangCD($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'comment date');
}

function convertDateLangGCD($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'guestbook date');
}

function convertDateLangAD($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'archive date');
}

function convertDateLangCH($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'calendar head');
}

function convertDateLangRTD($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'recent trackback date');
}

function convertDateLangRCD($argTarget, $argMother) {
	return convertDateFormat($argTarget, 'recent comment date');
}
?>