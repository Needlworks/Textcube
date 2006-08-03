<?
require 'correctTT.php';

function getTrackbacksWithPagingForOwner($owner, $category, $site, $ip, $search, $page, $count) {
	global $database;
	$sql = "SELECT t.*, c.name categoryName FROM {$database['prefix']}Trackbacks t LEFT JOIN {$database['prefix']}Entries e ON t.owner = e.owner AND t.entry = e.id AND e.draft = 0 LEFT JOIN {$database['prefix']}Categories c ON t.owner = c.owner AND e.category = c.id WHERE t.owner = $owner";
	if ($category > 0) {
		$categories = fetchQueryColumn("SELECT id FROM {$database['prefix']}Categories WHERE owner = $owner AND parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
	} else
		$sql .= ' AND e.category >= 0';
	if (!empty($site))
		$sql .= ' AND t.site = \'' . mysql_real_escape_string($site) . '\'';
	if (!empty($ip))
		$sql .= ' AND t.ip = \'' . mysql_real_escape_string($ip) . '\'';
	if (!empty($search)) {
		$search = escapeMysqlSearchString($search);
		$sql .= " AND (t.site LIKE '%$search%' OR t.subject LIKE '%$search%' OR t.excerpt LIKE '%$search%')";
	}
	$sql .= ' ORDER BY t.written DESC';
	return fetchWithPaging($sql, $page, $count);
}

function getTrackbacks($entry) {
	global $database, $owner;
	$trackbacks = array();
	$result = mysql_query("select * from {$database['prefix']}Trackbacks where owner = $owner AND entry = $entry order by written");
	while ($trackback = mysql_fetch_array($result))
		array_push($trackbacks, $trackback);
	return $trackbacks;
}

function getRecentTrackbacks($owner) {
	global $database;
	global $skinSetting;
	$trackbacks = array();
	$sql = doesHaveOwnership() ? "SELECT * FROM {$database['prefix']}Trackbacks WHERE owner = $owner ORDER BY written DESC LIMIT {$skinSetting['trackbacksOnRecent']}" : "SELECT t.* FROM {$database['prefix']}Trackbacks t, {$database['prefix']}Entries e WHERE t.owner = $owner AND t.owner = e.owner AND t.entry = e.id AND e.draft = 0 AND e.visibility >= 2 ORDER BY t.written DESC LIMIT {$skinSetting['trackbacksOnRecent']}";
	if ($result = mysql_query($sql)) {
		while ($trackback = mysql_fetch_array($result))
			array_push($trackbacks, $trackback);
	}
	return $trackbacks;
}

function receiveTrackback($owner, $entry, $title, $url, $excerpt, $site) {
	global $database;
	if (empty($url))
		return 5;
	requireComponent('Tattertools.Data.Post');
	if (!Post::doesAcceptTrackback($entry))
		return 3;
	
	requireComponent('Tattertools.Data.Filter');
	if (Filter::isFiltered('ip', $_SERVER['REMOTE_ADDR']) || Filter::isFiltered('url', $url))
		return 1;
	if (Filter::isFiltered('content', $excerpt))
		return 1;
	if (!fireEvent('AddingTrackback', true, array('entry' => $entry, 'url' => $url, 'site' => $site, 'title' => $title, 'excerpt' => $excerpt)))
		return 1;

	$title = correctTTForXmlText($title);
	$excerpt = correctTTForXmlText($excerpt);
	
	$url = mysql_lessen($url);
	$site = mysql_lessen($site);
	$title = mysql_lessen($title);
	$excerpt = mysql_lessen($excerpt);

	requireComponent('Tattertools.Data.Trackback');
	$trackback = new Trackback();
	$trackback->entry = $entry;
	$trackback->url = $url;
	$trackback->site = $site;
	$trackback->title = $title;
	$trackback->excerpt = $excerpt;
	if ($trackback->add())
		return 0;
	else
		return 4;
	return 0;
}

function deleteTrackback($owner, $id) {
	global $database;
	$entry = fetchQueryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND id = $id");
	if ($entry === null)
		return false;
	if (!executeQuery("DELETE FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($owner, $entry))
		return $entry;
	return false;
}

function sendTrackback($owner, $entryId, $url) {
	global $database, $defaultURL, $blog;
	requireComponent('Eolin.PHP.HTTPRequest');
	$entry = getEntry($owner, $entryId);
	if (!$entry)
		return false;
	$link = "$defaultURL/$entryId";
	$title = $entry['title'];
	$excerpt = UTF8::lessen(removeAllTags(stripHTML($entry['content'])), 255);
	$blogTitle = $blog['title'];
	$isNeedConvert = strpos($url, '/rserver.php?') !== false || strpos($url, 'blog.daum.net') !== false || strpos($url, 'blog.naver.com') !== false || strpos($url, 'blog.empas.com') !== false || strpos($url, '.egloos.com/tb/') !== false || strpos($url, 'blog.paran.com') !== false;
	if ($isNeedConvert) {
		$title = UTF8::convert($title);
		$excerpt = UTF8::convert($excerpt);
		$blogTitle = UTF8::convert($blogTitle);
		$content = "url=" . rawurlencode($link) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blogTitle) . "&excerpt=" . rawurlencode($excerpt);
		$request = new HTTPRequest('POST', $url);
		$request->contentType = 'application/x-www-form-urlencoded; charset=euc-kr';
		$isSuccess = $request->send($content);
	} else {
		$content = "url=" . rawurlencode($link) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blogTitle) . "&excerpt=" . rawurlencode($excerpt);
		$request = new HTTPRequest('POST', $url);
		$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
		$isSuccess = $request->send($content);
	}
	if ($isSuccess && (checkResponseXML($request->responseText) === 0)) {
		$url = mysql_real_escape_string($url);
		mysql_query("insert into {$database['prefix']}TrackbackLogs values ($owner, '', $entryId, '$url', UNIX_TIMESTAMP())");
		return true;
	}
	return false;
}

function getTrackbackLog($owner, $entry) {
	global $database;
	$result = mysql_query("select * from {$database['prefix']}TrackbackLogs where owner = $owner and entry = $entry");
	$str = '';
	while ($row = mysql_fetch_array($result)) {
		$str .= $row['id'] . ',' . $row['url'] . ',' . Timestamp::format5($row['written']) . '*';
	}
	return $str;
}

function getTrackbackLogs($owner, $entryId) {
	global $database;
	$logs = array();
	$result = mysql_query("select * from {$database['prefix']}TrackbackLogs where owner = $owner and entry = $entryId");
	while ($log = mysql_fetch_array($result))
		array_push($logs, $log);
	return $logs;
}

function deleteTrackbackLog($owner, $id) {
	global $database;
	$result = mysql_query("delete from {$database['prefix']}TrackbackLogs where owner = $owner and id = $id");
	return ($result && (mysql_affected_rows() == 1)) ? true : false;
}

function lastIndexOf($string, $item) {
	$index = strpos(strrev($string), strrev($item));
	if ($index) {
		$index = strlen($string) - strlen($item) - $index;
		return $index;
	} else
		return - 1;
}

function getURLForFilter($value) {
	$value = mysql_real_escape_string($value);
	$value = str_replace('http://', '', $value);
	$lastSlashPos = lastIndexOf($value, '/');
	if ($lastSlashPos > - 1) {
		$value = substr($value, 0, $lastSlashPos);
	}
	return $value;
}
?>