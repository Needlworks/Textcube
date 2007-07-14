<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function mysql_multi_query() {
	$result = false;
	foreach (func_get_args() as $query) {
		if (is_array($query)) {
			foreach ($query as $subquery)
				if (($result = mysql_tc_query($subquery)) === false)
					return false;
		} else if (($result = mysql_tc_query($query)) === false)
			return false;
	}
	return $result;
}

function escapeMysqlSearchString($str) {
	return is_string($str) ? str_replace('_', '\_', str_replace('%', '\%', mysql_tt_escape_string($str))) : $str;
}

function doesExistTable($tablename)
{
	$likeEscape = array ( '/_/' , '/%/' );
	$likeReplace = array ( '\\_' , '\\%' );
	$escapename = preg_replace($likeEscape, $likeReplace, $tablename);
	$result = mysql_tc_query("SHOW TABLES LIKE '$escapename' ");
	if ($result == false) return false;
	if (mysql_num_rows($result) > 0) return true;
	return false;
}
?>
