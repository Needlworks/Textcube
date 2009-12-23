<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/***** Functions below this line is symbolic links for legacy support *****/
/* NOTE : DO NOT USE THESE FUNCTIONS TO IMPLEMENT MODELS / PLUGINS. 
   THESE FUNCTIONS ARE DEPRECATED. NO LONGER USED. */

// From Tattertools 1.0 to Tattertools 1.0.x
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
		include_once (ROOT . '/library/function/iconv.php');
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

// From Tattertools 1.1.x to Textcube 1.5
function mysql_tt_escape_string($string, $link = null) {
	return POD::escapeString($string, $link);
}

function mysql_tc_escape_string($string, $link = null) {
	return POD::escapeString($string, $link);
}

function tc_escape_string($string, $link = null) {
	return POD::escapeString($string, $link);
}

function encodeURL($url) {
	global $service;
	if (isset($service['useEncodedURL']) && $service['useEncodedURL'])
		return str_replace('%2F', '/', rawurlencode($url));
	else
		return str_replace(array('%', ' ', '"', '#', '&', '\'', '<', '>', '?'), array('%25', '%20', '%22', '%23', '%26', '%27', '%3C', '%3E', '%3F'), $url);
}

function decodeURL($url) {
	global $service;
	if (isset($service['useEncodedURL']) && $service['useEncodedURL'])
		return rawurldecode($url);
	else
		return $url;
}

?>
