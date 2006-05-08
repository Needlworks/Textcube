<?php

function activatePlugin($name) {
	global $database, $owner, $activePlugins;
	if (in_array($name, $activePlugins))
		return true;
	if (!ereg('^[[:alnum:] _-]+$', $name))
		return false;
	if (!is_dir(ROOT . "/plugins/$name"))
		return false;
	if (!file_exists(ROOT . "/plugins/$name/index.xml") || !file_exists(ROOT . "/plugins/$name/index.php"))
		return false;
	$name = mysql_escape_string($name);
	mysql_query("INSERT INTO {$database['prefix']}Plugins VALUES ($owner, '$name', null)");
	return (mysql_affected_rows() == 1);
}

function deactivatePlugin($name) {
	global $database, $owner, $activePlugins;
	if (!in_array($name, $activePlugins))
		return;
	$name = mysql_escape_string($name);
	mysql_query("DELETE FROM {$database['prefix']}Plugins WHERE owner = $owner AND name = '$name'");
}
?>
