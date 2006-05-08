<?php
mysql_connect($database['server'], $database['username'], $database['password']);
mysql_select_db($database['database']);
if (mysql_query('SET CHARACTER SET utf8')) {
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
@mysql_query('SET SESSION collation_connection = \'utf8_general_ci\'');
?>
