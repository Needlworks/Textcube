<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Auth {
	function login($loginid, $password) {
		global $database, $owner;
		$loginid = mysql_tt_escape_string($loginid);
		$result = mysql_query("SELECT userid FROM {$database['prefix']}Users WHERE userid = $owner AND loginid = '$loginid' AND password = '" . md5($password) . "'");
		if ($result && (mysql_num_rows($result) == 1)) {
			mysql_query("UPDATE {$database['prefix']}Users SET lastLogin = UNIX_TIMESTAMP() WHERE loginid = '$loginid'");
			return true;
		}
		return false;
	}
}
?>
