<?
class User {
	/*@static@*/
	function getName($userid = null) {
		global $database, $owner;
		if (!isset($userid))
			$userid = $owner;
		return DBQuery::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = $userid");
	}
	
	/*@static@*/
	function getEmail($userid = null) {
		global $database, $owner;
		if (!isset($userid))
			$userid = $owner;
		return DBQuery::queryCell("SELECT loginid FROM {$database['prefix']}Users WHERE userid = $userid");
	}
	
	/*@static@*/
	function confirmPassword($password) {
		global $database, $owner;
		$password = md5($password);
		return DBQuery::queryExistence("SELECT userid FROM {$database['prefix']}Users WHERE userid = $owner AND password = '$password'");
	}
}
?>