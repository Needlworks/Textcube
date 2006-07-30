<?php

function getEntriesTotalCount($owner) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	return fetchQueryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0");
}

function getEntries($owner, $attributes = '*', $condition = false, $order = 'published DESC') {
	global $database;
	if (!empty($condition))
		$condition = 'AND ' . $condition;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	return fetchQueryAll("SELECT $attributes FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility $condition ORDER BY $order");
}

function getEntry($owner, $id, $draft = false) {
	global $database;
	if ($id == 0) {
		if ($draft) {
			if (!$id = getDraftEntryId())
				return;
		} else {
			if (!doesHaveOwnership())
				return;
			deleteAttachments($owner, 0);
			return array('id' => 0, 'draft' => 0, 'visibility' => 0, 'category' => 0, 'location' => '', 'title' => '', 'content' => '', 'acceptComment' => 1, 'acceptTrackback' => 1, 'published' => time());
		}
	}
	if ($draft) {
		$entry = fetchQueryRow("SELECT * FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 1");
		if (!$entry)
			return;
		if ($entry['published'] == 1)
			$entry['republish'] = true;
		else if ($entry['published'] != 0)
			$entry['appointed'] = $entry['published'];
		if ($id != 0)
			$entry['published'] = fetchQueryCell("SELECT published FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 0");
		return $entry;
	} else {
		$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
		$entry = fetchQueryRow("SELECT * FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 0 $visibility");
		if (!$entry)
			return;
		if ($entry['visibility'] < 0)
			$entry['appointed'] = $entry['published'];
		return $entry;
	}
}

function getEntryAttributes($owner, $id, $attributeNames) {
	global $database;
	
	if (stristr($attributeNames, "from") != false) // security check!
		return null;
	
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$attributes = fetchQueryRow("SELECT $attributeNames FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 0 $visibility");
	return $attributes;
}

function getEntryListByCategory($owner, $category) {
	global $database;
	if ($category === null)
		return array();
	if ($category > 0) {
		$categories = fetchQueryColumn("SELECT id FROM {$database['prefix']}Categories WHERE owner = $owner AND parent = $category");
		array_push($categories, $category);
		$cond = 'AND category IN (' . implode(', ', $categories) . ')';
	} else
		$cond = 'AND category >= 0';
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$sql = "SELECT owner,id,draft,visibility,category,title,slogan,
				location,password,acceptComment,acceptTrackback,
				published,created,modified,comments,trackbacks
			FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility $cond ORDER BY published DESC";
	return fetchQueryAll($sql);
}

function getEntryListByTag($owner, $tag) {
	global $database;
	if ($tag === null)
		return array();
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$sql = "SELECT e.* FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}TagRelations t ON e.id = t.entry AND e.owner = t.owner WHERE e.owner = $owner AND e.draft = 0 $visibility AND e.category >= 0 AND t.tag = $tag ORDER BY published DESC";
	return fetchQueryAll($sql);
}

function getEntryListByPeriod($owner, $period) {
	global $database;
	$cond = "AND published >= " . getTimeFromPeriod($period) . " AND published < " . getTimeFromPeriod(addPeriod($period));
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$sql = "SELECT * FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0 $cond ORDER BY published DESC";
	return fetchQueryAll($sql);
}

function getEntryListBySearch($owner, $search) {
	global $database;
	$search = escapeMysqlSearchString($search);
	$cond = empty($search) ? '' : "AND (title LIKE '%$search%' OR content LIKE '%$search%')";
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 1';
	$sql = "SELECT * FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0 $cond ORDER BY published DESC";
	return fetchQueryAll($sql);
}

function getEntriesWithPaging($owner, $page, $count) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$sql = "SELECT e.*, c.label categoryLabel FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id WHERE e.owner = $owner AND e.draft = 0 $visibility AND e.category >= 0 ORDER BY e.published DESC";
	return fetchWithPaging($sql, $page, $count);
}

function getEntriesWithPagingByCategory($owner, $category, $page, $count) {
	global $database, $folderURL, $suri;
	if ($category === null)
		return fetchWithPaging(null, $page, $count, "$folderURL/{$suri['value']}");
	if ($category > 0) {
		$categories = fetchQueryColumn("SELECT id FROM {$database['prefix']}Categories WHERE owner = $owner AND parent = $category");
		array_push($categories, $category);
		$cond = 'AND e.category IN (' . implode(', ', $categories) . ')';
	} else
		$cond = 'AND e.category >= 0';
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$sql = "SELECT e.*, c.label AS categoryLabel FROM {$database['prefix']}Entries AS e LEFT JOIN {$database['prefix']}Categories c ON e.category = c.id WHERE e.owner = $owner AND e.draft = 0 $visibility $cond ORDER BY e.published DESC";
	return fetchWithPaging($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getEntriesWithPagingByTag($owner, $tag, $page, $count) {
	global $database, $folderURL, $suri;
	if ($tag === null)
		return fetchWithPaging(null, $page, $count, "$folderURL/{$suri['value']}");
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$sql = "SELECT e.*, c.label categoryLabel FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id LEFT JOIN {$database['prefix']}TagRelations t ON e.id = t.entry AND e.owner = t.owner WHERE e.owner = $owner AND e.draft = 0 $visibility AND e.category >= 0 AND t.tag = $tag ORDER BY e.published DESC";
	return fetchWithPaging($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getEntriesWithPagingByNotice($owner, $page, $count) {
	global $database, $folderURL, $suri;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility = 2';
	$sql = "SELECT *, '" . _text('공지') . "' categoryLabel FROM {$database['prefix']}Entries WHERE owner = $owner $visibility AND category = -2 ORDER BY published DESC";
	return fetchWithPaging($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getEntriesWithPagingByPeriod($owner, $period, $page, $count) {
	global $database, $folderURL, $suri;
	$cond = "AND published >= " . getTimeFromPeriod($period) . " AND published < " . getTimeFromPeriod(addPeriod($period));
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$sql = "SELECT e.*, c.label categoryLabel FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id WHERE e.owner = $owner AND e.draft = 0 $visibility AND e.category >= 0 $cond ORDER BY e.published DESC";
	return fetchWithPaging($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getEntriesWithPagingBySearch($owner, $search, $page, $count) {
	global $database, $folderURL, $suri;
	$search = escapeMysqlSearchString($search);
	$cond = empty($search) ? '' : "AND (e.title LIKE '%$search%' OR e.content LIKE '%$search%')";
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 1';
	$sql = "SELECT e.*, c.label categoryLabel FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id WHERE e.owner = $owner AND e.draft = 0 $visibility AND e.category >= 0 $cond ORDER BY e.published DESC";
	return fetchWithPaging($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getEntriesWithPagingForOwner($owner, $category, $search, $page, $count) {
	global $database, $suri;
	$sql = "SELECT e.*, c.label categoryLabel, d.id draft FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.category = c.id AND e.owner = c.owner LEFT JOIN {$database['prefix']}Entries d ON e.owner = d.owner AND e.id = d.id AND d.draft = 1 WHERE e.owner = $owner AND e.draft = 0";
	if ($category > 0) {
		$categories = fetchQueryColumn("SELECT id FROM {$database['prefix']}Categories WHERE owner = $owner AND parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
	} else if ($category == -3) {
		$sql .= ' AND e.category = 0';
	} else if ($category == 0) {
		$sql .= ' AND e.category >= 0';
	} else {
		$sql .= ' AND e.category = '.$category;
	}
	if (!empty($search)) {
		$search = escapeMysqlSearchString($search);
		$sql .= " AND (e.title LIKE '%$search%' OR e.content LIKE '%$search%')";
	}
	$sql .= ' ORDER BY e.published DESC';
	return fetchWithPaging($sql, $page, $count);
}

function getEntryWithPaging($owner, $id, $isNotice = false) {
	global $database;
	global $folderURL;
	$entries = array();
	$paging = initPaging($folderURL, '/');
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$category = $isNotice ? 'e.category = -2' : 'e.category >= 0';
	$result = mysql_query("SELECT e.*, c.label categoryLabel FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id WHERE e.owner = $owner AND e.draft = 0 $visibility AND $category ORDER BY e.published DESC");
	if (!$result)
		return array($entries, $paging);
	$paging['pages'] = mysql_num_rows($result);
	for ($i = 1; $entry = mysql_fetch_array($result); $i++) {
		if ($entry['id'] != $id) {
			if (array_push($paging['before'], $entry['id']) > 4) if ($i == 5)
				$paging['first'] = array_shift($paging['before']);
			else
				array_shift($paging['before']);
			continue;
		}
		$paging['page'] = $i;
		array_push($entries, $entry);
		$paging['after'] = array();
		for ($i++; (count($paging['after']) < 4) && ($entry = mysql_fetch_array($result)); $i++)
			array_push($paging['after'], $entry['id']);
		if ($i < $paging['pages']) {
			while ($entry = mysql_fetch_array($result))
				$paging['last'] = $entry['id'];
		}
		if (count($paging['before']) > 0)
			$paging['prev'] = $paging['before'][count($paging['before']) - 1];
		if (isset($paging['after'][0]))
			$paging['next'] = $paging['after'][0];
		return array($entries, $paging);
	}
	$paging['page'] = $paging['pages'] + 1;
	return array($entries, $paging);
}

function getEntryWithPagingBySlogan($owner, $slogan) {
	global $database;
	global $blogURL;
	$entries = array();
	$paging = initPaging("$blogURL/entry", '/');
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$result = mysql_query("SELECT e.*, c.label categoryLabel FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id WHERE e.owner = $owner AND e.draft = 0 $visibility AND e.category >= 0 ORDER BY e.published DESC");
	if (!$result)
		return array($entries, $paging);
	$paging['pages'] = mysql_num_rows($result);
	for ($i = 1; $entry = mysql_fetch_array($result); $i++) {
		if ($entry['slogan'] != $slogan) {
			if (array_push($paging['before'], $entry['slogan']) > 4) if ($i == 5)
				$paging['first'] = array_shift($paging['before']);
			else
				array_shift($paging['before']);
			continue;
		}
		$paging['page'] = $i;
		array_push($entries, $entry);
		$paging['after'] = array();
		for ($i++; (count($paging['after']) < 4) && ($entry = mysql_fetch_array($result)); $i++)
			array_push($paging['after'], $entry['slogan']);
		if ($i < $paging['pages']) {
			while ($entry = mysql_fetch_array($result))
				$paging['last'] = $entry['slogan'];
		}
		if (count($paging['before']) > 0)
			$paging['prev'] = $paging['before'][count($paging['before']) - 1];
		if (isset($paging['after'][0]))
			$paging['next'] = $paging['after'][0];
		return array($entries, $paging);
	}
	$paging['page'] = $paging['pages'] + 1;
	return array($entries, $paging);
}

function getSlogan($slogan) {
	$slogan = ereg_replace('-+', ' ', $slogan);
	$slogan = ereg_replace('[!-/:-@[-`{-~]+', '', $slogan);
	$slogan = ereg_replace('[[:space:]]+', '-', $slogan);
	$slogan = trim($slogan, '-');
	return strlen($slogan) > 0 ? $slogan : 'X';
}

function getRecentEntries($owner) {
	global $database, $skinSetting;
	$entries = array();
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$result = mysql_query("SELECT id, title, comments FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0 ORDER BY published DESC LIMIT {$skinSetting['entriesOnRecent']}");
	while ($entry = mysql_fetch_array($result)) {
		array_push($entries, $entry);
	}
	return $entries;
}

function addEntry($owner, $entry) {
	global $database, $blog;
	$entry['title'] = mysql_lessen(trim($entry['title']));
	$entry['location'] = mysql_lessen(trim($entry['location']));
	if(empty($entry['slogan'])) {
		$slogan = $slogan0 = getSlogan($entry['title']);
	} else {
		$slogan = $slogan0 = getSlogan($entry['slogan']);
	}
	$result = mysql_query("SELECT slogan FROM {$database['prefix']}Entries WHERE owner = $owner AND slogan = '$slogan' LIMIT 1");
	for ($i = 1; mysql_num_rows($result) > 0; $i++) {
		if ($i > 100)
			return false;
		$slogan = "$slogan0-$i";
		$result = mysql_query("SELECT slogan FROM {$database['prefix']}Entries WHERE owner = $owner AND slogan = '$slogan' LIMIT 1");
	}
	$title = mysql_escape_string($entry['title']);
	$content = mysql_escape_string(filterJavaScript($entry['content']));
	$password = generatePassword();
	$location = mysql_escape_string($entry['location']);
	if (isset($entry['published']) && is_numeric($entry['published']) && ($entry['published'] >= 2)) {
		$published = $entry['published'];
		$entry['visibility'] = 0 - $entry['visibility'];
	} else {
		$published = 'UNIX_TIMESTAMP()';
	}
	$id = getDraftEntryId();
	if ($id === null)
		$id = 0;
	$result = mysql_query("INSERT INTO {$database['prefix']}Entries VALUES (
			$owner,
			$id,
			0,
			{$entry['visibility']},
			{$entry['category']},
			'$title',
			'$slogan',
			'$content',
			'$location',
			'$password',
			{$entry['acceptComment']},
			{$entry['acceptTrackback']},
			$published,
			UNIX_TIMESTAMP(),
			UNIX_TIMESTAMP(),
			0,
			0)");
	if (!$result)
		return false;
	$id = mysql_insert_id();
	mysql_query("DELETE FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 1");
	mysql_query("UPDATE {$database['prefix']}Attachments SET parent = $id WHERE owner = $owner AND parent = 0");
	updateEntriesOfCategory($owner, $entry['category']);
	if ($entry['visibility'] == 3)
		syndicateEntry($id, true);
	if ($entry['visibility'] >= 2)
		clearRSS();
	if (!empty($entry['tag'])) {
		$tags = getTagsWithEntryString($entry['tag']);
		addTagsWithEntryId($owner, $id, $tags);
	}
	return $id;
}

function getDraftEntryId($id = 0) {
	global $database, $owner;
	if ($id)
		return fetchQueryCell("SELECT id FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 1");
	else
		return fetchQueryCell("SELECT d.id FROM {$database['prefix']}Entries d LEFT JOIN {$database['prefix']}Entries e ON d.owner = e.owner AND d.id = e.id AND e.draft = 0 WHERE d.owner = $owner AND d.draft = 1 AND e.id IS NULL ORDER BY d.id LIMIT 1");
}

function updateEntry($owner, $entry) {
	global $database;
	global $blog;
	//if (($entry['category'] == - 1) && (($entry['visibility'] == 1) || ($entry['visibility'] == 3)))
	//	return false;
	$entry['title'] = mysql_lessen(trim($entry['title']));
	$entry['location'] = mysql_lessen(trim($entry['location']));

	$tags = getTagsWithEntryString($entry['tag']);
	modifyTagsWithEntryId($owner, $entry['id'], $tags);
	
	$location = mysql_escape_string($entry['location']);
	$title = mysql_escape_string($entry['title']);
	$content = mysql_escape_string(filterJavaScript($entry['content']));
	switch ($entry['published']) {
		case 0:
			$published = 'published';
			break;
		case 1:
			$published = 'UNIX_TIMESTAMP()';
			break;
		default:
			$published = $entry['published'];
			$entry['visibility'] = 0 - $entry['visibility'];
			break;
	}
	$result = mysql_query("UPDATE {$database['prefix']}Entries
			SET
				visibility = {$entry['visibility']},
				category = {$entry['category']},
				location = '$location',
				title = '$title',
				content = '$content',
				acceptComment = {$entry['acceptComment']},
				acceptTrackback = {$entry['acceptTrackback']},
				published = $published,
				modified = UNIX_TIMESTAMP()
			WHERE owner = $owner AND id = {$entry['id']} AND draft = 0");
	if ($result)
		mysql_query("DELETE FROM {$database['prefix']}Entries WHERE owner = $owner AND id = {$entry['id']} AND draft = 1");
	updateEntriesOfCategory($owner, $entry['category']);
	if ($entry['visibility'] == 3)
		syndicateEntry($entry['id'], true);
	mysql_query("UPDATE {$database['prefix']}Attachments SET parent = {$entry['id']} WHERE owner = $owner AND parent = 0");
	if ($entry['visibility'] >= 2)
		clearRSS();
	return $result ? true : false;
}

function saveDraftEntry($entry) {
	global $database, $owner;
//	if ($entry['category'] == - 1)
//		return false;
	$entry['title'] = mysql_lessen(trim($entry['title']));
	$entry['location'] = mysql_lessen(trim($entry['location']));
	$location = mysql_escape_string($entry['location']);
	$title = mysql_escape_string($entry['title']);
	$content = mysql_escape_string(filterJavaScript($entry['content']));
	$draft = getDraftEntryId($entry['id']);
	if ($draft) {
		$result = mysql_query("UPDATE {$database['prefix']}Entries
				SET
					visibility = {$entry['visibility']},
					category = {$entry['category']},
					title = '$title',
					slogan = '',
					content = '$content',
					location = '$location',
					acceptComment = {$entry['acceptComment']},
					acceptTrackback = {$entry['acceptTrackback']},
					published = {$entry['published']},
					modified = UNIX_TIMESTAMP()
				WHERE owner = $owner AND id = $draft AND draft = 1");
		if (!$result)
			return false;
	} else {
		$password = generatePassword();
		$result = mysql_query("INSERT INTO {$database['prefix']}Entries
				VALUES (
					$owner,
					{$entry['id']},
					1,
					{$entry['visibility']},
					{$entry['category']},
					'$title',
					'',
					'$content',
					'$location',
					'$password',
					{$entry['acceptComment']},
					{$entry['acceptTrackback']},
					{$entry['published']},
					UNIX_TIMESTAMP(),
					UNIX_TIMESTAMP(),
					0,
					0
				)");
		if (!$result)
			return false;
		$entry['id'] = mysql_insert_id();
	}
	return $entry['id'];
}

function updateTrackbacksOfEntry($owner, $id) {
	global $database;
	$trackbacks = fetchQueryCell("SELECT COUNT(*) FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND entry = $id");
	if ($trackbacks === null)
		return false;
	return executeQuery("UPDATE {$database['prefix']}Entries SET trackbacks = $trackbacks WHERE owner = $owner AND id = $id");
}

function deleteEntry($owner, $id) {
	global $database, $blog;
	$target = getEntry($owner, $id);
	if (fetchQueryCell("SELECT visibility FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id") == 3)
		syndicateEntry($id, false);
	$result = mysql_query("DELETE FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id");
	if (mysql_affected_rows() > 0) {
		$result = mysql_query("DELETE FROM {$database['prefix']}Comments WHERE owner = $owner AND entry = $id");
		$result = mysql_query("DELETE FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND entry = $id");
		$result = mysql_query("DELETE FROM {$database['prefix']}TrackbackLogs WHERE owner = $owner AND entry = $id");

		updateEntriesOfCategory($owner, $target['category']);
		deleteAttachments($owner, $id);
		
		deleteTagsWithEntryId($owner, $id);
		
		clearRSS();
		return true;
	}
	return false;
}

function changeCategoryOfEntries($owner, $entries, $category) {
	global $database;
	$targets = array_unique(preg_split('/,/', $entries, -1, PREG_SPLIT_NO_EMPTY));
	if ( count($targets)<1 || !is_numeric($category) ) 
		return false;
	$sql = "UPDATE  {$database['prefix']}Entries SET category = $category WHERE owner = $owner AND id IN (";
	for ($i = 0; $i < count($targets); $i++) {
		if(is_numeric($targets[$i]))
			$sql .="{$targets[$i]},";
	}
	$sql =trim($sql, ','). ")";
	executeQuery($sql);
	if(mysql_affected_rows() == 0)
		return false;	
	if(updateEntriesOfCategory($owner)) {
		clearRSS();
		return true;	
	}
	return false;
}

function setEntryVisibility($id, $visibility) {
	global $database, $owner;
	if (($visibility < 0) || ($visibility > 3))
		return false;
	list($oldVisibility, $category) = fetchQueryRow("SELECT visibility, category FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 0");
	if ($oldVisibility === null)
		return false;
	if (($category == - 1) && (($visibility == 1) || ($visibility == 3)))
		return false;
	if ($visibility == $oldVisibility)
		return true;
	$result = mysql_query("UPDATE {$database['prefix']}Entries SET visibility = $visibility, modified = UNIX_TIMESTAMP() WHERE owner = $owner AND id = $id");
	if (!$result)
		return false;
	if (mysql_affected_rows() == 0)
		return true;
	if ($oldVisibility == 3)
		syndicateEntry($id, false);
	else if ($visibility == 3) {
		if (!syndicateEntry($id)) {
			mysql_query("UPDATE {$database['prefix']}Entries SET visibility = $oldVisibility, modified = UNIX_TIMESTAMP() WHERE owner = $owner AND id = $id");
			return false;
		}
	}
	if ($category >= 0) {
		if ((($oldVisibility >= 2) && ($visibility < 2)) || (($oldVisibility < 2) && ($visibility >= 2)))
			clearRSS();
		if ($category > 0)
			updateEntriesOfCategory($owner, $category);
	}
	return true;
}

function protectEntry($id, $password) {
	global $database, $owner;
	$password = mysql_escape_string($password);
	$result = mysql_query("UPDATE {$database['prefix']}Entries SET password = '$password', modified = UNIX_TIMESTAMP() WHERE owner = $owner AND id = $id AND visibility = 1");
	return ($result && (mysql_affected_rows() > 0));
}

function syndicateEntry($id, $syndicate = true) {
	global $database, $owner, $defaultURL;
	$mode = ($syndicate ? 1 : 0);
	requireComponent('Eolin.PHP.HTTPRequest');
	$request = new HTTPRequest('POST', TATTERTOOLS_SYNC_URL);
	$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
	if ($request->send("mode=$mode&path=" . urlencode("$defaultURL/sync/$id")) && (checkResponseXML($request->responseText) === 0))
		return true;
	return false;
}

function publishEntries() {
	global $database, $owner;
	$entries = fetchQueryAll("SELECT id, visibility FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND visibility < 0 AND published < UNIX_TIMESTAMP()");
	if (count($entries) == 0)
		return;
	foreach ($entries as $i => $entry) {
		$result = mysql_query("UPDATE {$database['prefix']}Entries SET visibility = 0 WHERE owner = $owner AND id = {$entry['id']} AND draft = 0");
		if ($result && (mysql_affected_rows() > 0))
			setEntryVisibility($entry['id'], abs($entry['visibility']));
	}
}

function getTagsWithEntryString($entryTag) 
{
	global $database;
	$tags = explode(',', $entryTag);
	
	$ret = array();
	
	foreach ($tags as $tag) {
		$tag = mysql_lessen($tag, 255, '');
		$tag = str_replace('&quot;', '"', $tag);
		$tag = str_replace('&#39;', '\'', $tag);
		$tag = preg_replace('/ +/', ' ', $tag);
		$tag = preg_replace('/[\x00-\x1f]|[\x7f]/', '', $tag);
		$tag = preg_replace('/^(-|\s)+/', '', $tag);
		$tag = preg_replace('/(-|\s)+$/', '', $tag);
		$tag = trim($tag);
		
		array_push($ret, $tag);
	}
	
	return $ret;
}

function getEntryVisibilityName($visibility) {
	switch (abs($visibility)) {
		case 0:
			return _text('비공개');
		case 1:
			return _text('보호');
		case 2:
			return _text('공개');
		case 3:default:
			return _text('발행');
	}
}
?>
