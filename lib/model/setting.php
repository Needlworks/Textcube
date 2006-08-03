<?php
function getServiceSetting($name, $default = null) {
	global $database;
	$value = DBQuery::queryCell("SELECT value FROM {$database['prefix']}ServiceSettings WHERE name = '".mysql_real_escape_string($name)."'");
	return ($value === null) ? $default : $value;
}

function setServiceSetting($name, $value) {
	global $database;
	$name = mysql_real_escape_string($name);
	$value = mysql_real_escape_string($value);
	return DBQuery::execute("REPLACE INTO {$database['prefix']}ServiceSettings VALUES('$name', '$value')");
}

function removeServiceSetting($name) {
	global $database;
	return DBQuery::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name = '".mysql_real_escape_string($name)."'");
}

function getUserSetting($name, $default = null) {
	global $database, $owner;
	$value = DBQuery::queryCell("SELECT value FROM {$database['prefix']}UserSettings WHERE user = $owner AND name = '".mysql_real_escape_string($name)."'");
	return ($value === null) ? $default : $value;
}

function setUserSetting($name, $value) {
	global $database, $owner;
	$name = mysql_real_escape_string($name);
	$value = mysql_real_escape_string($value);
	return DBQuery::execute("REPLACE INTO {$database['prefix']}UserSettings VALUES($owner, '$name', '$value')");
}

function removeUserSetting($name) {
	global $database, $owner;
	return DBQuery::execute("DELETE FROM {$database['prefix']}UserSettings WHERE user = $owner AND name = '".mysql_real_escape_string($name)."'");
}
?>
