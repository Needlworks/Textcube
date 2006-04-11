<?

function getKeywordByName($owner, $name) {
	return false;
}

function getKeywordCount($owner) {
	return 0;
}

function getKeywordNames($owner) {
	return array();
}

function getKeywords($owner) {
	return;
}

function getKeywordsWithPaging($owner, $search, $page, $count) {
	global $database, $folderURL, $suri;
	$sql = "SELECT * FROM {$database['prefix']}Entries WHERE FALSE";
	return fetchWithPaging($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getKeylog($owner, $keyword) {
	return array();
}
?>