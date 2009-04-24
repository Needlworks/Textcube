<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getNoticesWithPaging($blogid, $search, $page, $count) {
	global $database, $folderURL, $suri;
	$aux = '';
	if (($search !== true) && $search) {
		$search = Data_IAdapter::escapeSearchString($search);
		$aux = "AND (title LIKE '%$search%' OR content LIKE '%$search%')";
	}
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 1';
	$sql = "SELECT * FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 $visibility AND category = -2 $aux ORDER BY published DESC";
	return fetchWithPaging($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getNotice($blogid, $id) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 1';
	return Data_IAdapter::queryAll("SELECT id, title, content, published FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 $visibility AND category = -2 AND id = $id");
}

function getNotices($blogid) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 1';
	return Data_IAdapter::queryAll("SELECT id, title, slogan, published FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 $visibility AND category = -2 ORDER BY published DESC");
}
?>
