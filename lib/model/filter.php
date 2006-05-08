<?php

function closeFilter($owner, $mode, $value) {
	global $database;
	switch ($mode) {
		case 'sitename':
			$table = 'URLFilters';
			$column = 'url';
			break;
		case 'name':
			$table = 'GuestFilters';
			$column = 'name';
			break;
		case 'address':
			$table = 'HostFilters';
			$column = 'address';
			break;
		case 'contents':
			$table = 'ContentFilters';
			$column = 'word';
			break;
		default:
			return false;
	}
	$value = mysql_escape_string($value);
	if ($value == '')
		return false;
	$sql = "DELETE FROM {$database['prefix']}$table WHERE owner = $owner AND $column = '$value'";
	$result = mysql_query($sql);
	return ($result && mysql_affected_rows() > 0) ? true : false;
}

function openFilter($owner, $mode, $value) {
	global $database;
	$value = mysql_escape_string($value);
	switch ($mode) {
		case 'sitename':
			$table = 'URLFilters';
			$column = 'url';
			break;
		case 'name':
			$table = 'GuestFilters';
			$column = 'name';
			break;
		case 'address':
			$table = 'HostFilters';
			$column = 'address';
			break;
		case 'contents':
			$table = 'ContentFilters';
			$column = 'word';
			break;
		default:
			return false;
	}
	if ($value == '')
		return false;
	if ($table == 'URLFilters')
		$value = str_replace('http://', '', $value);
	$value = mysql_escape_string($value);
	$sql = "INSERT INTO {$database['prefix']}$table (owner, $column, written) VALUES ($owner, '$value', UNIX_TIMESTAMP())";
	$result = mysql_query($sql);
	return ($result && mysql_affected_rows() > 0) ? true : false;
}

function modifyFilter($owner, $mode, $oldValue, $newValue) {
	global $database;
	switch ($mode) {
		case 'sitename':
			$table = 'URLFilters';
			$column = 'url';
			break;
		case 'name':
			$table = 'GuestFilters';
			$column = 'name';
			break;
		case 'address':
			$table = 'HostFilters';
			$column = 'address';
			break;
		case 'contents':
			$table = 'ContentFilters';
			$column = 'word';
			break;
		default:
			return false;
	}
	$newValue = mysql_escape_string($newValue);
	$oldValue = mysql_escape_string($oldValue);
	$sql = "UPDATE {$database['prefix']}$table SET 
					owner	= $owner, 
					$column	= '$newValue'
				WHERE $column= '$oldValue'";
	$result = mysql_query($sql);
	return $result ? true : false;
}

function getFilters($owner, $mode) {
	global $database;
	switch ($mode) {
		case 'sitename':
			$table = 'URLFilters';
			$column = 'url';
			break;
		case 'name':
			$table = 'GuestFilters';
			$column = 'name';
			break;
		case 'address':
			$table = 'HostFilters';
			$column = 'address';
			break;
		case 'contents':
			$table = 'ContentFilters';
			$column = 'word';
			break;
		default:
			return false;
	}
	$result = mysql_query("SELECT * FROM {$database['prefix']}$table WHERE owner = $owner");
	if (!$result)
		return false;
	$returnValue = array();
	while ($row = mysql_fetch_array($result)) {
		array_push($returnValue, $row);
	}
	return $returnValue;
}

function isFiltered($owner, $mode, $value) {
	global $database;
	$value = mysql_escape_string($value);
	switch ($mode) {
		case 'sitename':
			$table = 'URLFilters';
			$column = 'url';
			$value = str_replace('http://', '', $value);
			$lastSlashPos = lastIndexOf($value, '/');
			if ($lastSlashPos > - 1) {
				$value = substr($value, 0, $lastSlashPos);
			}
			break;
		case 'name':
			$table = 'GuestFilters';
			$column = 'name';
			break;
		case 'address':
			$table = 'HostFilters';
			$column = 'address';
			break;
		case 'contents':
			$table = 'ContentFilters';
			$column = 'word';
			break;
		default:
			return false;
	}
	if ($mode == 'contents') {
		$result = mysql_query("select $column from {$database['prefix']}$table WHERE owner = $owner");
		while ($row = mysql_fetch_row($result)) {
			if (eregi($row[0], $value)) {
				return true;
			}
		}
		return false;
	} else {
		return mysql_result(mysql_query("select count(*) from {$database['prefix']}$table WHERE owner = $owner AND $column = '$value'"), 0, 0);
	}
}
?>
