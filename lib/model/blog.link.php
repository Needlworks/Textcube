<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getLinks($blogid) {
	global $database;
	$links = array();
	if ($result = DBQuery::query("select * from {$database['prefix']}Links where blogid = $blogid ORDER BY name")) {
		while ($link = mysql_fetch_array($result))
			array_push($links, $link);
	}
	return $links;
}

function getLink($blogid, $id) {
	global $database;
	return DBQuery::queryRow("select * from {$database['prefix']}Links where blogid = $blogid and id = $id");
}

function deleteLink($blogid, $id) {
	global $database;
	$result = DBQuery::query("delete from {$database['prefix']}Links where blogid = $blogid and id = $id");
	return ($result && (mysql_affected_rows() == 1)) ? true : false;
}

function toggleVisibility($blogid, $id) {
	global $database;
	if (DBQuery::queryCell("SELECT visible FROM {$database['prefix']}Links WHERE blogid = $blogid AND id = $id") == 1) {
		$visible = 0;
	} else {
		$visible = 1;
	}
	$result = DBQuery::query("update {$database['prefix']}Links set visible = $visible where blogid = $blogid and id = $id");
	return array( ($result && (mysql_affected_rows() == 1)) ? true : false, $visible );
}

function addLink($blogid, $link) {
	global $database;
	$name = UTF8::lessenAsEncoding(trim($link['name']), 255);
	$url = UTF8::lessenAsEncoding(trim($link['url']), 255);
	if (empty($name) || empty($url))
		return - 1;
	$name = tc_escape_string($name);
	$url = tc_escape_string($url);
	$rss = isset($link['rss']) ? tc_escape_string(UTF8::lessenAsEncoding(trim($link['rss']), 255)) : '';
	if (DBQuery::queryCell("SELECT id FROM {$database['prefix']}Links WHERE blogid = $blogid AND url = '$url'"))
		return 1;
	if (DBQuery::execute("INSERT INTO {$database['prefix']}Links VALUES ($blogid, null, '$name', '$url', '$rss', UNIX_TIMESTAMP())"))
		return 0;
	else
		return - 1;
}

function updateLink($blogid, $link) {
	global $database;
	$id = $link['id'];
	$name = UTF8::lessenAsEncoding(trim($link['name']), 255);
	$url = UTF8::lessenAsEncoding(trim($link['url']), 255);
	if (empty($name) || empty($url))
		return false;
	$name = tc_escape_string($name);
	$url = tc_escape_string($url);
	$rss = isset($link['rss']) ? tc_escape_string(UTF8::lessenAsEncoding(trim($link['rss']), 255)) : '';
	return DBQuery::execute("update {$database['prefix']}Links
				set
					name = '$name',
					url = '$url',
					rss = '$rss',
					written = UNIX_TIMESTAMP()
				where
					blogid = $blogid and id = {$link['id']}");
}

function updateXfn($blogid, $links) {
	global $database;
	$ids = Array();
	foreach( $links as $k => $v ) {
		if( substr($k,0,3) == 'xfn' ) {
			$id = substr( $k, 3 );
			$xfn = tc_escape_string($v);
			DBQuery::execute("update {$database['prefix']}Links
				set
					xfn = '$xfn',
					written = UNIX_TIMESTAMP()
				where
					blogid = $blogid and id = $id");
		}
	}
}
?>
