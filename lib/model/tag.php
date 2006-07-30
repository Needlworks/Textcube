<?php

function getTagId($owner, $name) {
	global $database;
	$name = mysql_escape_string($name);
	return fetchQueryCell("SELECT id FROM {$database['prefix']}Tags WHERE name = '$name'");
}

function getTags($entry) {
	global $database, $owner;
	$tags = array();
	if (doesHaveOwnership())
		$result = mysql_query("SELECT * FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r WHERE t.id = r.tag AND r.entry = $entry AND r.owner = $owner GROUP BY t.name ORDER BY t.name");
	else
		$result = mysql_query("SELECT * FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r, `{$database['prefix']}Entries` e WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.entry = $entry AND r.owner = $owner GROUP BY t.name ORDER BY t.name");
	if ($result) {
		while ($tag = mysql_fetch_array($result))
			array_push($tags, $tag);
	}
	return $tags;
}

function getRandomTags($owner) {
	global $database, $skinSetting;
	$tags = array();
	$aux = ($skinSetting['tagsOnTagbox'] == - 1) ? '' : "limit {$skinSetting['tagsOnTagbox']}";
	if ($skinSetting['tagboxAlign'] == 1) {
		if (doesHaveOwnership())
			$result = mysql_query("SELECT `name`, count(*) `cnt` FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r WHERE t.id = r.tag and r.owner = $owner GROUP BY t.name ORDER BY cnt DESC $aux");
		else
			$result = mysql_query("SELECT `name`, count(*) `cnt` FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r, `{$database['prefix']}Entries` e WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.owner = $owner GROUP BY t.name ORDER BY `cnt` DESC $aux");
	} else if ($skinSetting['tagboxAlign'] == 2) {
		if (doesHaveOwnership())
			$result = mysql_query("SELECT DISTINCT name FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r WHERE t.id = r.tag AND r.owner = $owner ORDER BY t.name $aux");
		else
			$result = mysql_query("SELECT DISTINCT name FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r, `{$database['prefix']}Entries` e WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.owner = $owner ORDER BY t.name $aux");
	} else {
		if (doesHaveOwnership())
			$result = mysql_query("SELECT `name` FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r WHERE t.id = r.tag AND r.owner = $owner GROUP BY t.name ORDER BY RAND() $aux");
		else
			$result = mysql_query("SELECT `name` FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r, `{$database['prefix']}Entries` e WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.owner = $owner GROUP BY t.name ORDER BY RAND() $aux");
	}
	if ($result) {
		while (list($tag) = mysql_fetch_row($result))
			array_push($tags, $tag);
	}
	return $tags;
}

function getSiteTags($owner) {
	global $database;
	$names = array();
	if (doesHaveOwnership())
		$result = mysql_query("SELECT `name` FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r WHERE t.id = r.tag AND r.owner = $owner GROUP BY t.name ORDER BY t.name");
	else
		$result = mysql_query("SELECT `name` FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r, `{$database['prefix']}Entries` e WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.owner = $owner GROUP BY t.name ORDER BY t.name");
	if ($result) {
		while (list($name) = mysql_fetch_array($result))
			array_push($names, $name);
	}
	return $names;
}

function getTagFrequencyRange() {
	global $database, $owner;
	$max = $min = 0;
	if (doesHaveOwnership())
		$result = mysql_query("SELECT count(r.entry) `cnt` FROM `{$database['prefix']}TagRelations` r WHERE r.owner = $owner GROUP BY r.tag ORDER BY `cnt` DESC LIMIT 1");
	else
		$result = mysql_query("SELECT count(r.entry) `cnt` FROM `{$database['prefix']}TagRelations` r, `{$database['prefix']}Entries` e WHERE r.entry = e.id AND e.visibility > 0 AND r.owner = $owner GROUP BY r.tag ORDER BY `cnt` DESC LIMIT 1");
	if ($result) {
		if (list($count) = mysql_fetch_array($result))
			$max = $count;
	}
	if (doesHaveOwnership())
		$result = mysql_query("SELECT count(r.entry) `cnt` FROM `{$database['prefix']}TagRelations` r WHERE r.owner = $owner GROUP BY r.tag ORDER BY `cnt` LIMIT 1");
	else
		$result = mysql_query("SELECT count(r.entry) `cnt` FROM `{$database['prefix']}TagRelations` r, `{$database['prefix']}Entries` e WHERE r.entry = e.id AND e.visibility > 0 AND r.owner = $owner GROUP BY r.tag ORDER BY `cnt` LIMIT 1");
	if ($result) {
		if (list($count) = mysql_fetch_array($result))
			$min = $count;
	}
	return array($max, $min);
}

function getTagFrequency($tag, $max, $min) {
	global $database, $owner;
	if (doesHaveOwnership())
		$count = fetchQueryCell("SELECT count(*) FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r WHERE t.id = r.tag AND r.owner = $owner AND t.name = '" . mysql_escape_string($tag) . "'");
	else
		$count = fetchQueryCell("SELECT count(*) FROM `{$database['prefix']}Tags` t, `{$database['prefix']}TagRelations` r, `{$database['prefix']}Entries` e WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.owner = $owner AND t.name = '" . mysql_escape_string($tag) . "'");
	$dist = $max / 3;
	if ($count == $min)
		return 5;
	else if ($count == $max)
		return 1;
	else if ($count >= $min + ($dist * 2))
		return 2;
	else if ($count >= $min + $dist)
		return 3;
	else
		return 4;
}

function deleteGarbageTags() {
	return; // because of bug, deprecated.
	/*global $database, $owner;
	$gc = fetchQueryColumn("SELECT t.id FROM {$database['prefix']}Tags t 
	LEFT JOIN {$database['prefix']}TagRelations r ON t.id = r.tag WHERE r.owner = $owner AND r.tag IS NULL");
	foreach ($gc as $g)
		DBQuery::query("DELETE FROM {$database['prefix']}Tags WHERE id = $g");*/
}

function suggestLocalTags($owner, $filter) {
	global $database;
	$tags = array();
	$result = mysql_query("select distinct name, count(*) cnt from {$database['prefix']}Tags, {$database['prefix']}TagRelations where id = tag and owner = $owner and $filter group by name order by cnt desc limit 10");
	if ($result) {
		while ($tag = mysql_fetch_array($result))
			array_push($tags, $tag[0]);
	}
	return $tags;
}
?>
