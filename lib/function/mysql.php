<?

function mysql_multi_query() {
	$result = false;
	foreach (func_get_args() as $query) {
		if (is_array($query)) {
			foreach ($query as $subquery)
				if (($result = mysql_query($subquery)) === false)
					return false;
		} else if (($result = mysql_query($query)) === false)
			return false;
	}
	return $result;
}

function fetchQueryAll($query, $type = MYSQL_BOTH) {
	$rows = array();
	if ($result = mysql_query($query)) {
		while ($row = mysql_fetch_array($result, $type))
			array_push($rows, $row);
		mysql_free_result($result);
	}
	return $rows;
}

function fetchQueryRow($query) {
	if ($result = mysql_query($query)) {
		if ($row = mysql_fetch_array($result)) {
			mysql_free_result($result);
			return $row;
		}
		mysql_free_result($result);
	}
	return;
}

function fetchQueryColumn($query) {
	$column = array();
	if ($result = mysql_query($query)) {
		while ($row = mysql_fetch_row($result))
			array_push($column, $row[0]);
		mysql_free_result($result);
	}
	return $column;
}

function fetchQueryCell($query) {
	if ($result = mysql_query($query)) {
		list($cell) = mysql_fetch_row($result);
		mysql_free_result($result);
		return $cell;
	}
	return;
}

function executeQuery($query) {
	return mysql_query($query) ? true : false;
}
?>