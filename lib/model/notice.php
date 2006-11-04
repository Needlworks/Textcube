<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getNoticesWithPaging($owner, $search, $page, $count) {
	global $database, $folderURL, $suri;
	$aux = '';
	if (($search !== true) && $search) {
		$search = escapeMysqlSearchString($search);
		$aux = "AND (title LIKE '%$search%' OR content LIKE '%$search%')";
	}
	$visibility = doesHaveOwnership() ? '' : 'AND visibility = 2';
	$sql = "SELECT * FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category = -2 $aux ORDER BY published DESC";
	return fetchWithPaging($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getNotice($owner, $id) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility = 2';
	return fetchQueryAll("SELECT id, title, content, published FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category = -2 AND id = $id");
}

function getNotices($owner) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility = 2';
	return fetchQueryAll("SELECT id, title, published FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category = -2 ORDER BY published DESC");
}
?>
