<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
mysql_connect($database['server'], $database['username'], $database['password']);
mysql_select_db($database['database']);

if (DBQuery::query('SET CHARACTER SET utf8')) {
	$database['utf8'] = true;
	function mysql_lessen($str, $length = 255, $tail = '..') {
		return UTF8::lessen($str, $length, $tail);
	}
} else {
	$database['utf8'] = false;
	function mysql_lessen($str, $length = 255, $tail = '..') {
		return UTF8::lessenAsByte($str, $length, $tail);
	}
}
@DBQuery::query('SET SESSION collation_connection = \'utf8_general_ci\'');

if (function_exists('mysql_real_escape_string') && (mysql_real_escape_string('ㅋ') == 'ㅋ')) {
	function mysql_tt_escape_string($string, $link = null) {
		return is_null($link) ? mysql_real_escape_string($string) : mysql_real_escape_string($string, $link);
	}
} else {
	function mysql_tt_escape_string($string, $link = null) {
		return mysql_escape_string($string);
	}
}
?>
