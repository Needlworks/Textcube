<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

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
	return DBQuery::queryRow("select * from {$database['prefix']}Links where owner = $owner and id = $id");
}

function deleteLink($owner, $id) {
	global $database;
	$result = mysql_query("delete from {$database['prefix']}Links where owner = $owner and id = $id");
	return ($result && (mysql_affected_rows() == 1)) ? true : false;
}

function addLink($owner, $link) {
	global $database;
	$name = mysql_lessen(trim($link['name']), 255);
	$url = mysql_lessen(trim($link['url']), 255);
	if (empty($name) || empty($url))
		return - 1;
	$name = mysql_tt_escape_string($name);
	$url = mysql_tt_escape_string($url);
	$rss = isset($link['rss']) ? mysql_tt_escape_string(mysql_lessen(trim($link['rss']), 255)) : '';
	if (DBQuery::queryCell("SELECT id FROM {$database['prefix']}Links WHERE owner = $owner AND url = '$url'"))
		return 1;
	if (DBQuery::execute("INSERT INTO {$database['prefix']}Links VALUES ($owner, null, '$name', '$url', '$rss', UNIX_TIMESTAMP())"))
		return 0;
	else
		return - 1;
}

function updateLink($owner, $link) {
	global $database;
	$id = $link['id'];
	$name = mysql_lessen(trim($link['name']), 255);
	$url = mysql_lessen(trim($link['url']), 255);
	if (empty($name) || empty($url))
		return false;
	$name = mysql_tt_escape_string($name);
	$url = mysql_tt_escape_string($url);
	$rss = isset($link['rss']) ? mysql_tt_escape_string(mysql_lessen(trim($link['rss']), 255)) : '';
	return DBQuery::execute("update {$database['prefix']}Links
				set
					name = '$name',
					url = '$url',
					rss = '$rss',
					written = UNIX_TIMESTAMP()
				where
					owner = $owner and id = {$link['id']}");
}
?>
