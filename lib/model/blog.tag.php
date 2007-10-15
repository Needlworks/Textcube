<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getTagId($blogid, $name) {
	global $database;
	$name = tc_escape_string($name);
	return DBQuery::queryCell("SELECT id FROM {$database['prefix']}Tags WHERE name = '$name'");
}

function getTags($blogid, $entry) {
	global $database;
	$tags = array();
	if (doesHaveOwnership())
		$result = DBQuery::query("SELECT * FROM `{$database['prefix']}Tags` t, 
			`{$database['prefix']}TagRelations` r 
			WHERE t.id = r.tag AND r.entry = $entry AND r.blogid = $blogid 
			GROUP BY r.tag 
			ORDER BY t.name");
	else
		$result = DBQuery::query("SELECT * FROM `{$database['prefix']}Tags` t,
			`{$database['prefix']}TagRelations` r, 
			`{$database['prefix']}Entries` e 
			WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.entry = $entry AND r.blogid = $blogid 
			GROUP BY r.tag 
			ORDER BY t.name");
	if ($result) {
		while ($tag = mysql_fetch_array($result))
			array_push($tags, $tag);
	}
	return $tags;
}

function getRandomTags($blogid) {
	global $database, $skinSetting;
	$tags = array();
	$aux = ($skinSetting['tagsOnTagbox'] == - 1) ? '' : "limit {$skinSetting['tagsOnTagbox']}";
	if ($skinSetting['tagboxAlign'] == 1) { // order by count
		if (doesHaveOwnership())
			$tags = DBQuery::queryAll("SELECT `name`, count(*) `cnt` FROM `{$database['prefix']}Tags` t, 
				`{$database['prefix']}TagRelations` r 
				WHERE t.id = r.tag and r.blogid = $blogid 
				GROUP BY r.tag 
				ORDER BY cnt DESC $aux");
		else
			$tags = DBQuery::queryAll("SELECT `name`, count(*) `cnt` FROM `{$database['prefix']}Tags` t, 
				`{$database['prefix']}TagRelations` r, 
				`{$database['prefix']}Entries` e 
				WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid 
				GROUP BY r.tag 
				ORDER BY `cnt` DESC $aux");
	} else if ($skinSetting['tagboxAlign'] == 2) {  // order by name
		if (doesHaveOwnership())
			$tags = DBQuery::queryAll("SELECT DISTINCT name, count(*) 'cnt' FROM `{$database['prefix']}Tags` t, 
				`{$database['prefix']}TagRelations` r 
				WHERE t.id = r.tag AND r.blogid = $blogid 
				GROUP BY r.tag 
				ORDER BY t.name $aux");
		else
			$tags = DBQuery::queryAll("SELECT DISTINCT name, count(*) `cnt` FROM `{$database['prefix']}Tags` t, 
				`{$database['prefix']}TagRelations` r,
				`{$database['prefix']}Entries` e 
				WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid 
				GROUP BY r.tag 
				ORDER BY t.name $aux");
	} else { // random
		if (doesHaveOwnership())
			$tags = DBQuery::queryAll("SELECT `name`, count(*) `cnt` FROM `{$database['prefix']}Tags` t,
				`{$database['prefix']}TagRelations` r
				WHERE t.id = r.tag AND r.blogid = $blogid
				GROUP BY r.tag ORDER BY RAND() $aux");
		else
			$tags = DBQuery::queryAll("SELECT `name`, count(*) `cnt` FROM `{$database['prefix']}Tags` t,
				`{$database['prefix']}TagRelations` r,
				`{$database['prefix']}Entries` e
				WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid 
				GROUP BY r.tag 
				ORDER BY RAND() $aux");
	}
	return $tags;
}

function getSiteTags($blogid) {
	global $database;
	if (doesHaveOwnership())
		$names = DBQuery::queryColumn("SELECT `name` FROM `{$database['prefix']}Tags` t, 
			`{$database['prefix']}TagRelations` r 
			WHERE t.id = r.tag AND r.blogid = $blogid 
			GROUP BY r.tag 
			ORDER BY t.name 
			LIMIT 2000");
	else
		$names = DBQuery::queryColumn("SELECT `name` FROM `{$database['prefix']}Tags` t, 
			`{$database['prefix']}TagRelations` r,
			`{$database['prefix']}Entries` e
			WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = $blogid 
			GROUP BY r.tag 
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
		$max = DBQuery::queryCell("SELECT count(r.entry) `cnt` FROM `{$database['prefix']}TagRelations` r 
			WHERE r.blogid = $blogid 
			GROUP BY r.tag 
			ORDER BY `cnt` 
			DESC LIMIT 1");
	else
		$max = DBQuery::queryCell("SELECT count(r.entry) `cnt` FROM `{$database['prefix']}TagRelations` r
			INNER JOIN `{$database['prefix']}Entries` e 
				ON e.blogid = r.blogid AND e.visibility > 0 AND r.entry = e.id
			WHERE r.blogid = $blogid 
			GROUP BY r.tag 
			ORDER BY `cnt` 
			DESC LIMIT 1");
/*	if (doesHaveOwnership())
		$min = DBQuery::queryCell("SELECT count(r.entry) `cnt` FROM `{$database['prefix']}TagRelations` r 
			WHERE r.blogid = $blogid 
			GROUP BY r.tag 
			ORDER BY `cnt` 
			LIMIT 1");
	else
		$min = DBQuery::queryCell("SELECT count(r.entry) `cnt` FROM `{$database['prefix']}TagRelations` r
			INNER JOIN `{$database['prefix']}Entries` e 
				ON e.blogid = r.blogid AND e.visibility > 0 AND r.entry = e.id
			WHERE r.blogid = $blogid 
			GROUP BY r.tag 
			ORDER BY `cnt` 
			LIMIT 1");*/
	$max = ($max == null ? 0 : $max);
	//$min = ($min == null ? 0 : $min);
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
			$count = DBQuery::queryCell("SELECT count(*) FROM `{$database['prefix']}Tags` t, 
				`{$database['prefix']}TagRelations` r 
				WHERE t.id = r.tag AND r.blogid = $blogid AND t.name = '" . tc_escape_string($tag['name']) . "'");
		else
			$count = DBQuery::queryCell("SELECT count(*) FROM `{$database['prefix']}Tags` t, 
				`{$database['prefix']}TagRelations` r, 
				`{$database['prefix']}Entries` e 
				WHERE r.entry = e.id AND e.visibility > 0 AND t.id = r.tag AND r.blogid = e.blogid AND r.blogid = $blogid AND t.name = '" . tc_escape_string($tag['name']) . "'");
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
	$result = DBQuery::query("select distinct name, count(*) cnt from {$database['prefix']}Tags, 
		{$database['prefix']}TagRelations 
		where id = tag and blogid = $blogid and $filter 
		group by tag 
		order by cnt 
		desc limit 10");
	if ($result) {
		while ($tag = mysql_fetch_array($result))
			array_push($tags, $tag[0]);
	}
	return $tags;
}

function removeEmptyTagHelper($var)
{
	return (strlen($var) > 0);
}

function addTagsWithEntryId($blogid, $entry, /*string array*/$taglist)
{
	global $database;
	requireComponent('Needlworks.Cache.PageCache');
	if ($taglist == null)
		return;
		
	if (false) removeEmptyTagHelper(""); // for optimizer

	$tmptaglist = array_filter($taglist, 'removeEmptyTagHelper');

	if (count($tmptaglist) == 0)
		return;

	$taglist = array();
	foreach($tmptaglist as $tag) {
		$tag = tc_escape_string(UTF8::lessenAsEncoding(trim($tag), 255));
		array_push($taglist, $tag);
	}

	// step 1. Insert Tags
	$tagliststr = '(\'' . implode('\') , (\'', $taglist) . '\')';
	DBQuery::execute("INSERT IGNORE INTO {$database['prefix']}Tags (name) VALUES $tagliststr ");

	// the point of Race condition
	// if other entry is deleted, some missing tags can be exist so they are not related with this entry.
	
	// step 2. Insert Relations
	$tagliststr =  '\'' . implode('\' , \'', $taglist) . '\'';
	/*
	DBQuery::execute("INSERT INTO {$database['prefix']}TagRelations
			(SELECT $blogid, t.id, $entry FROM {$database['prefix']}Tags as t 
			WHERE 
				name in ( $tagliststr ) AND  
				t.id NOT IN 
				( SELECT tag FROM {$database['prefix']}TagRelations WHERE 
				(tag = t.id) AND (entry = $entry) AND (blogid = $blogid)
			)
	)");
	*/
	// For MySQL 3, Simple Query Version
	$tagIDs = DBQuery::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
	$tagrelations = array();
	foreach($tagIDs as $tagid)
	{
		array_push($tagrelations, " ($blogid, $tagid, $entry) ");
		CacheControl::flushTag($tagid);	
	}
	$tagRelationStr = implode(', ', $tagrelations);
	DBQuery::execute("INSERT IGNORE INTO {$database['prefix']}TagRelations VALUES $tagRelationStr");
}

function modifyTagsWithEntryId($blogid, $entry, /*string array*/$taglist)
{
	global $database;
	if ($taglist == null)
		$taglist = array();
		
	if (false) removeEmptyTagHelper(""); // for optimizer
		
	$tmptaglist = array_filter($taglist, 'removeEmptyTagHelper');
	$taglist = array();
	foreach($tmptaglist as $tag) {
		$tag = tc_escape_string(trim($tag));
		array_push($taglist, $tag);
	}
	
	// step 1. Get deleted Tag
	$tmpoldtaglist = DBQuery::queryColumn("SELECT name FROM {$database['prefix']}Tags
		LEFT JOIN {$database['prefix']}TagRelations ON tag = id 
		WHERE blogid = $blogid AND entry = $entry");
	if ($tmpoldtaglist == null)
		$tmpoldtaglist = array();
	$oldtaglist = array();
	foreach($tmpoldtaglist as $tag) {
		$tag = tc_escape_string(UTF8::lessenAsEncoding(trim($tag), 255));
		array_push($oldtaglist, $tag);
	}
	
	$deletedTagList = array_diff($oldtaglist, $taglist);
	$insertedTagList = array_diff($taglist, $oldtaglist);
	
	// step 2. Insert Tag
	if (count($insertedTagList) > 0) 
	{
		$tagliststr = '(\'' . implode('\') , (\'', $insertedTagList) . '\')';
		DBQuery::execute("INSERT IGNORE INTO {$database['prefix']}Tags (name) VALUES $tagliststr ");
	
	// step 3. Insert Relation
		$tagliststr =  '\'' . implode('\' , \'', $insertedTagList) . '\'';
		/*
		DBQuery::execute("INSERT INTO {$database['prefix']}TagRelations
			(SELECT $blogid, t.id, $entry FROM {$database['prefix']}Tags as t 
			WHERE 
				name in ( $tagliststr ) AND  
				t.id NOT IN 
					( SELECT tag FROM {$database['prefix']}TagRelations WHERE 
					(tag = t.id) AND (entry = $entry) AND (blogid = $blogid)
				)
			)");
		*/
		// For MySQL 3, Simple Query Version
		$tagIDs = DBQuery::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
		$tagrelations = array();
		foreach($tagIDs as $tagid)
		{
			array_push($tagrelations, " ($blogid, $tagid, $entry) ");
			CacheControl::flushTag($tagid);	
		}
		$tagRelationStr = implode(', ', $tagrelations);
		DBQuery::execute("INSERT IGNORE INTO {$database['prefix']}TagRelations VALUES $tagRelationStr");
	}
	
	// step 4. Delete Tag
	if (count($deletedTagList) > 0)
	{
		// small step, get tag id list
		$tagliststr =  '\'' . implode('\' , \'', $deletedTagList) . '\'';
		$t1list = DBQuery::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
		if ($t1list == null) 
			return; // What?
		// Flushing pageCache
		foreach($t1list as $tagids) {
			CacheControl::flushTag($tagids);
		}
		// Make string
		$t1liststr = implode(', ', $t1list);
		$taglist = DBQuery::queryColumn(
				"SELECT tag FROM {$database['prefix']}TagRelations 
						WHERE blogid = $blogid AND entry = $entry AND tag in ( $t1liststr )");
		if ($taglist == null) 
			return; // What?
		
		// now delete tag
		$tagliststr = implode(', ', $taglist);
	
	// step 5. Delete Relation
		DBQuery::execute("DELETE FROM {$database['prefix']}TagRelations WHERE blogid = $blogid AND entry = $entry AND tag in ( $tagliststr )");
	
	// step 6. Delete Tag
		$nottargets = DBQuery::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE tag in ( $tagliststr )");
		if (count($nottargets) > 0) {
			$nottargetstr	= implode(', ', $nottargets);
			DBQuery::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) AND id NOT IN ( $nottargetstr )");
		} else {
				DBQuery::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr )");
		}

	}
}

function deleteTagsWithEntryId($blogid, $entry)
{
	global $database;
	$taglist = DBQuery::queryColumn("SELECT tag 
		FROM {$database['prefix']}TagRelations 
		WHERE blogid = $blogid 
			AND entry = $entry");
	if ($taglist != null) {
		$tagliststr = implode(',', $taglist);
		
		foreach($taglist as $tagid) {
			CacheControl::flushTag($tagid);
		}
		DBQuery::execute("DELETE FROM {$database['prefix']}TagRelations 
			WHERE blogid = $blogid 
				AND entry = $entry");
		$nottargets = DBQuery::queryColumn("SELECT DISTINCT tag 
			FROM {$database['prefix']}TagRelations 
			WHERE tag in ( $tagliststr )");
		if (count($nottargets) > 0) {
			$nottargetstr	= implode(', ', $nottargets);
			DBQuery::execute("DELETE FROM {$database['prefix']}Tags 
				WHERE id IN ( $tagliststr ) 
					AND id NOT IN ( $nottargetstr )");
		} else {
			DBQuery::execute("DELETE FROM {$database['prefix']}Tags 
				WHERE id IN ( $tagliststr )");
		}
	}
}
?>
