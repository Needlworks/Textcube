<?php
class Auth {
	function login($loginid, $password) {
		global $database, $owner;
		$loginid = mysql_escape_string($loginid);
		$result = mysql_query("SELECT userid FROM {$database['prefix']}Users WHERE userid = $owner AND loginid = '$loginid' AND `password` = '" . md5($password) . "'");
		if ($result && (mysql_num_rows($result) == 1)) {
			mysql_query("UPDATE {$database['prefix']}Users SET lastLogin = UNIX_TIMESTAMP() WHERE loginid = '$loginid'");
			return true;
		}
		return false;
	}
}
?>
