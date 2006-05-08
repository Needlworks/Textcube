<?php

function getTagId($owner, $name) {
	global $database;
	$name = mysql_escape_string($name);
	return fetchQueryCell("SELECT id FROM {$database['prefix']}Tags WHERE name = '$name'");
}

function getTags($entry) {
	global $database, $owner;
	$tags = array();
	$result = mysql_query("select * from {$database['prefix']}Tags, {$database['prefix']}TagRelations where id = tag and entry = $entry and owner = $owner group by name order by name");
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
	if ($skinSetting['tagboxAlign'] == 1)
		$result = mysql_query("select name, count(*) cnt from {$database['prefix']}Tags, {$database['prefix']}TagRelations where id = tag and owner = $owner GROUP BY name ORDER BY cnt DESC $aux");
	else if ($skinSetting['tagboxAlign'] == 2)
		$result = mysql_query("select distinct name from {$database['prefix']}Tags, {$database['prefix']}TagRelations where id = tag and owner = $owner ORDER BY name $aux");
	else
		$result = mysql_query("select name from {$database['prefix']}Tags, {$database['prefix']}TagRelations where id = tag and owner = $owner GROUP BY name ORDER BY RAND() $aux");
	if ($result) {
		while (list($tag) = mysql_fetch_row($result))
			array_push($tags, $tag);
	}
	return $tags;
}

function getSiteTags($owner) {
	global $database;
	$names = array();
	$result = mysql_query("select name from {$database['prefix']}Tags t, {$database['prefix']}TagRelations r where t.id = r.tag and r.owner = $owner group by name order by name");
	if ($result) {
		while (list($name) = mysql_fetch_array($result))
			array_push($names, $name);
	}
	return $names;
}

function getTagFrequencyRange() {
	global $database, $owner;
	$max = $min = 0;
	$result = mysql_query("select count(entry) cnt from {$database['prefix']}TagRelations where owner = $owner group by tag order by cnt desc limit 1");
	if ($result) {
		if (list($count) = mysql_fetch_array($result))
			$max = $count;
	}
	$result = mysql_query("select count(entry) cnt from {$database['prefix']}TagRelations where owner = $owner group by tag order by cnt limit 1");
	if ($result) {
		if (list($count) = mysql_fetch_array($result))
			$min = $count;
	}
	return array($max, $min);
}

function getTagFrequency($tag, $max, $min) {
	global $database, $owner;
	$count = fetchQueryCell("select count(*) from {$database['prefix']}Tags t, {$database['prefix']}TagRelations r where t.id=r.tag and r.owner = $owner and t.name = '" . mysql_escape_string($tag) . "'");
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
	global $database, $owner;
	$gc = fetchQueryColumn("SELECT t.id FROM {$database['prefix']}Tags t LEFT JOIN {$database['prefix']}TagRelations r ON t.id = r.tag WHERE r.owner = $owner AND r.tag IS NULL");
	foreach ($gc as $g)
		mysql_query("DELETE FROM {$database['prefix']}Tags WHERE id = $g");
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
