<?

function getLinks($owner) {
	global $database;
	$links = array();
	if ($result = mysql_query("select * from {$database['prefix']}Links where owner = $owner ORDER BY name")) {
		while ($link = mysql_fetch_array($result))
			array_push($links, $link);
	}
	return $links;
}

function getLink($owner, $id) {
	global $database;
	return fetchQueryRow("select * from {$database['prefix']}Links where owner = $owner and id = $id");
}

function deleteLink($owner, $id) {
	global $database;
	$result = mysql_query("delete from {$database['prefix']}Links where owner = $owner and id = $id");
	return ($result && (mysql_affected_rows() == 1)) ? true : false;
}

function addLink($owner, $link) {
	global $database;
	$name = trim($link['name']);
	$url = trim($link['url']);
	if (empty($name) || empty($url))
		return - 1;
	$name = mysql_real_escape_string($name);
	$url = mysql_real_escape_string($url);
	$rss = isset($link['rss']) ? mysql_real_escape_string(trim($link['rss'])) : '';
	if (fetchQueryCell("SELECT id FROM {$database['prefix']}Links WHERE owner = $owner AND url = '$url'"))
		return 1;
	if (executeQuery("INSERT INTO {$database['prefix']}Links VALUES ($owner, null, '$name', '$url', '$rss', UNIX_TIMESTAMP())"))
		return 0;
	else
		return - 1;
}

function updateLink($owner, $link) {
	global $database;
	$id = $link['id'];
	$name = trim($link['name']);
	$url = trim($link['url']);
	if (empty($name) || empty($url))
		return false;
	$name = mysql_real_escape_string($name);
	$url = mysql_real_escape_string($url);
	$rss = isset($link['rss']) ? mysql_real_escape_string(trim($link['rss'])) : '';
	return executeQuery("update {$database['prefix']}Links
				set
					name = '$name',
					url = '$url',
					rss = '$rss',
					written = UNIX_TIMESTAMP()
				where
					owner = $owner and id = {$link['id']}");
}
?>