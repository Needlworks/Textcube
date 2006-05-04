<?
/// Copyright © 2004-2006, Tatter & Company. All rights reserved.

function isUTF8($str) {
	return UTF8::validate($str);
}

function utf8Lessen($str, $length, $tail = '..') {
	return UTF8::lessen($str, $length, $tail);
}

function iconvWrapper($from, $to, $str) {
	if (function_exists('iconv'))
		return @iconv($from, $to, $str);
	else if (function_exists('mb_convert_encoding'))
		return @mb_convert_encoding($str, $to, $from);
	else {
		include_once (ROOT . '/iconv.php');
		if (function_exists('iconv'))
			return @iconv($from, $to, $str);
		else
			return false;
	}
}

function getTimestamp3($time) {
	return Timestamp::format3($time);
}

function getTimestamp2($time) {
	return Timestamp::format2($time);
}
?>