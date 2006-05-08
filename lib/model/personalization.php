<?php

function getPersonalization($owner, $column) {
	global $database;
	$column = mysql_escape_string($column);
	$sql = "SELECT $column FROM {$database['prefix']}Personalization WHERE owner = $owner";
	$result = fetchQueryCell($sql);
	if (empty($result)) {
		if (0 == fetchQueryCell("SELECT count(*) FROM {$database['prefix']}Personalization WHERE owner = $owner")) {
			executeQuery("INSERT INTO `{$database['prefix']}Personalization` (`owner`) VALUES (\"" . $owner . "\")");
			$sql = "SELECT $column FROM {$database['prefix']}Personalization WHERE owner = $owner";
			$result = fetchQueryCell($sql);
			return $result;
		}
	}
	return $result;
}

function setPersonalization($owner, $column, $value) {
	global $database;
	$column = mysql_escape_string($column);
	$value = is_numeric($value) ? $value : '\'' . mysql_escape_string($value) . '\'';
	$sql = "UPDATE {$database['prefix']}Personalization SET `$column`=$value WHERE owner = $owner";
	$result = executeQuery($sql);
	if (!$result) {
		if (0 == fetchQueryCell("SELECT count(*) FROM {$database['prefix']}Personalization WHERE owner = $owner")) {
			return executeQuery("INSERT INTO `{$database['prefix']}Personalization` (`owner`) VALUES (\"" . $owner . "\")");
		}
	}
	return executeQuery($sql);
}
?>
