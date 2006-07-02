<?php
function getUserSetting($name, $default = null) {
	global $database, $owner;
	$value = DBQuery::queryCell("SELECT value FROM {$database['prefix']}UserSettings WHERE user = $owner AND name = '".mysql_escape_string($name)."'");
	return ($value === null) ? $default : $value;
}

function setUserSetting($name, $value) {
	global $database, $owner;
	$name = mysql_escape_string($name);
	$value = mysql_escape_string($value);
	return DBQuery::execute("REPLACE INTO {$database['prefix']}UserSettings VALUES($owner, '$name', '$value')");
}

function removeUserSetting($name) {
	global $database, $owner;
	return DBQuery::execute("DELETE FROM {$database['prefix']}UserSettings WHERE user = $owner AND name = '".mysql_escape_string($name)."'");
}
?>
