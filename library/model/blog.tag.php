<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getTagId($blogid, $name) {
	global $database;
	$name = POD::escapeString($name);
	return POD::queryCell("SELECT id FROM {$database['prefix']}Tags WHERE name = '$name'");
}

function getTagById($blogid, $id) {
	global $database;
	return POD::queryCell("SELECT name FROM {$database['prefix']}Tags WHERE id = $id");
}

function getTags($blogid, $entry) {
	global $database;
	$tags = array();
	if (doesHaveOwnership())
		$result = POD::query("SELECT t.* FROM {$database['prefix']}Tags t
			INNER JOIN {$database['prefix']}TagRelations r ON r.blogid = $blogid AND r.tag = t.id AND r.entry = $entry AND r.tag = t.id
			GROUP BY r.tag, t.id, t.name 
			ORDER BY t.name");
	else
		$result = POD::query("SELECT t.* FROM {$database['prefix']}Tags t
			INNER JOIN {$database['prefix']}TagRelations r ON r.blogid = $blogid AND r.entry = $entry AND r.tag = t.id
			INNER JOIN {$database['prefix']}Entries e ON e.id = r.entry AND e.visibility > 0
			GROUP BY r.tag, t.id, t.name
			ORDER BY t.name");
	if ($result) {
		while ($tag = POD::fetch($result))
			array_push($tags, $tag);
	}
	return $tags;
}

function getRandomTags($blogid) {
	global $database, $skinSetting;
	$tags = array();
	$aux = ($skinSetting['tagsontagbox'] == - 1) ? '' : "LIMIT {$skinSetting['tagsontagbox']}";
	if ($skinSetting['tagboxalign'] == 1) { // order by count
		if (doesHaveOwnership())
			$tags = POD::queryAll("SELECT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t 
				INNER JOIN {$database['prefix']}TagRelations r ON r.blogid = $blogid AND r.tag = t.id
				GROUP BY r.tag, t.name, t.id 
				ORDER BY cnt DESC $aux");
		else
			$tags = POD::queryAll("SELECT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t,
				{$database['prefix']}TagRelations r, 
				{$database['prefix']}Entries e 
				WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid AND e.blogid = $blogid 
				GROUP BY r.tag, t.name, t.id
				ORDER BY cnt DESC $aux");
	} else if ($skinSetting['tagboxalign'] == 2) {  // order by name
		if (doesHaveOwnership())
			$tags = POD::queryAll("SELECT DISTINCT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t, 
				{$database['prefix']}TagRelations r 
				WHERE t.id = r.tag AND r.blogid = $blogid 
				GROUP BY r.tag, t.name, t.id
				ORDER BY t.name $aux");
		else
			$tags = POD::queryAll("SELECT DISTINCT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t, 
				{$database['prefix']}TagRelations r,
				{$database['prefix']}Entries e 
				WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid AND e.blogid = $blogid
				GROUP BY r.tag, t.name, t.id
				ORDER BY t.name $aux");
	} else { // random
		if (doesHaveOwnership())
			$tags = POD::queryAll("SELECT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t,
				{$database['prefix']}TagRelations r
				WHERE t.id = r.tag AND r.blogid = $blogid
				GROUP BY r.tag, t.name, t.id
				ORDER BY RAND() $aux");
		else
			$tags = POD::queryAll("SELECT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t,
				{$database['prefix']}TagRelations r,
				{$database['prefix']}Entries e
				WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid AND e.blogid = $blogid
				GROUP BY r.tag, t.name, t.id
				ORDER BY RAND() $aux");
	}
	return $tags;
}

function getSiteTags($blogid) {
	global $database;
	if (doesHaveOwnership())
		$names = POD::queryAll("SELECT t.id, name FROM {$database['prefix']}Tags t, 
			{$database['prefix']}TagRelations r 
			WHERE t.id = r.tag AND r.blogid = $blogid 
			GROUP BY r.tag, t.id, name
			ORDER BY t.name 
			LIMIT 2000");
	else
		$names = POD::queryAll("SELECT t.id, t.name FROM {$database['prefix']}Tags t, 
			{$database['prefix']}TagRelations r,
			{$database['prefix']}Entries e
			WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid 
			GROUP BY r.tag, t.id, t.name
			ORDER BY t.name 
			LIMIT 2000");
	if(!empty($names)) return $names;
	else $names = array();
	return $names;
}

function getTagFrequencyRange() {
	global $database;
	$blogid = getBlogId();
	$max = $min = 0;
	if (doesHaveOwnership())
		$max = POD::queryCell("SELECT count(r.entry) AS cnt FROM {$database['prefix']}TagRelations r 
			WHERE r.blogid = $blogid 
			GROUP BY r.tag
			ORDER BY cnt 
			DESC LIMIT 1");
	else
		$max = POD::queryCell("SELECT count(r.entry) AS cnt FROM {$database['prefix']}TagRelations r
			INNER JOIN {$database['prefix']}Entries e ON r.blogid = e.blogid AND e.visibility > 0 AND r.entry = e.id
			WHERE r.blogid = $blogid 
			GROUP BY r.tag 
			ORDER BY cnt 
			DESC LIMIT 1");
/*	if (doesHaveOwnership())
		$min = POD::queryCell("SELECT count(r.entry) cnt FROM {$database['prefix']}TagRelations r 
			WHERE r.blogid = $blogid 
			GROUP BY r.tag 
			ORDER BY cnt 
			LIMIT 1");
	else
		$min = POD::queryCell("SELECT count(r.entry) cnt FROM {$database['prefix']}TagRelations r
			INNER JOIN {$database['prefix']}Entries e 
				ON e.blogid = r.blogid AND e.visibility > 0 AND r.entry = e.id
			WHERE r.blogid = $blogid 
			GROUP BY r.tag 
			ORDER BY cnt 
			LIMIT 1");*/
	$max = ($max === null ? 0 : $max);
	//$min = ($min === null ? 0 : $min);
	$min = 1;
	return array($max, $min);
}

function getTagFrequency($tag, $max, $min) {
	global $database;
	$blogid = getBlogId();
	if (is_array($tag) && array_key_exists('cnt', $tag)) $count = $tag['cnt'];
	else {
		if (!is_array($tag)) {
			$tag = array('name' => $tag);
		} 
		if (doesHaveOwnership())
			$count = POD::queryCell("SELECT count(*) FROM {$database['prefix']}Tags t
				INNER JOIN {$database['prefix']}TagRelations r ON r.tag = t.id AND r.blogid = $blogid
				WHERE t.name = '" . POD::escapeString($tag['name']) . "'");
		else
			$count = POD::queryCell("SELECT count(*) FROM {$database['prefix']}Tags t
				INNER JOIN {$database['prefix']}TagRelations r ON r.tag = t.id AND r.blogid = $blogid 
				INNER JOIN {$database['prefix']}Entries e ON e.blogid = r.blogid AND e.id = r.entry AND e.visibility > 0 
				WHERE t.name = '" . POD::escapeString($tag['name']) . "'");
	}
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

function suggestLocalTags($blogid, $filter) {
	global $database;
	$tags = array();
	$result = POD::queryAll("SELECT DISTINCT t.name, count(*) AS cnt 
		FROM {$database['prefix']}Tags t 
		INNER JOIN {$database['prefix']}TagRelations r
		WHERE t.id = r.tag 
			AND r.blogid = $blogid
			AND $filter
		GROUP BY tag, t.name
		ORDER BY cnt 
		DESC LIMIT 10");
	if ($result) {
		foreach($result as $tag)
			array_push($tags, $tag[0]);
	}
	return $tags;
}
?>
