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
			GROUP BY r.tag 
			ORDER BY t.name");
	else
		$result = POD::query("SELECT t.* FROM {$database['prefix']}Tags t
			INNER JOIN {$database['prefix']}TagRelations r ON r.blogid = $blogid AND r.entry = $entry AND r.tag = t.id
			INNER JOIN {$database['prefix']}Entries e ON e.id = r.entry AND e.visibility > 0
			GROUP BY r.tag 
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
	$aux = ($skinSetting['tagsOnTagbox'] == - 1) ? '' : "limit {$skinSetting['tagsOnTagbox']}";
	if ($skinSetting['tagboxAlign'] == 1) { // order by count
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
	} else if ($skinSetting['tagboxAlign'] == 2) {  // order by name
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
		$tag = POD::escapeString(UTF8::lessenAsEncoding(trim($tag), 255));
		array_push($taglist, $tag);
	}

	// step 1. Insert Tags
	$tagliststr = '(\'' . implode('\') , (\'', $taglist) . '\')';
	POD::execute("INSERT IGNORE INTO {$database['prefix']}Tags (name) VALUES $tagliststr ");

	// the point of Race condition
	// if other entry is deleted, some missing tags can be exist so they are not related with this entry.
	
	// step 2. Insert Relations
	$tagliststr =  '\'' . implode('\' , \'', $taglist) . '\'';
	/*
	POD::execute("INSERT INTO {$database['prefix']}TagRelations
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
	$tagIDs = POD::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
	$tagrelations = array();
	foreach($tagIDs as $tagid)
	{
		array_push($tagrelations, " ($blogid, $tagid, $entry) ");
		CacheControl::flushTag($tagid);	
	}
	$tagRelationStr = implode(', ', $tagrelations);
	POD::execute("INSERT IGNORE INTO {$database['prefix']}TagRelations VALUES $tagRelationStr");
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
		$tag = POD::escapeString(trim($tag));
		array_push($taglist, $tag);
	}
	
	// step 1. Get deleted Tag
	$tmpoldtaglist = POD::queryColumn("SELECT name FROM {$database['prefix']}Tags
		LEFT JOIN {$database['prefix']}TagRelations ON tag = id 
		WHERE blogid = $blogid AND entry = $entry");
	if ($tmpoldtaglist === null)
		$tmpoldtaglist = array();
	$oldtaglist = array();
	foreach($tmpoldtaglist as $tag) {
		$tag = POD::escapeString(UTF8::lessenAsEncoding(trim($tag), 255));
		array_push($oldtaglist, $tag);
	}
	
	$deletedTagList = array_diff($oldtaglist, $taglist);
	$insertedTagList = array_diff($taglist, $oldtaglist);
	
	// step 2. Insert Tag
	if (count($insertedTagList) > 0) 
	{
		$tagliststr = '(\'' . implode('\') , (\'', $insertedTagList) . '\')';
		POD::execute("INSERT IGNORE INTO {$database['prefix']}Tags (name) VALUES $tagliststr ");
	
	// step 3. Insert Relation
		$tagliststr =  '\'' . implode('\' , \'', $insertedTagList) . '\'';
		/*
		POD::execute("INSERT INTO {$database['prefix']}TagRelations
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
		$tagIDs = POD::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
		$tagrelations = array();
		foreach($tagIDs as $tagid)
		{
			array_push($tagrelations, " ($blogid, $tagid, $entry) ");
			CacheControl::flushTag($tagid);	
		}
		$tagRelationStr = implode(', ', $tagrelations);
		POD::execute("INSERT IGNORE INTO {$database['prefix']}TagRelations VALUES $tagRelationStr");
	}
	
	// step 4. Delete Tag
	if (count($deletedTagList) > 0)
	{
		// small step, get tag id list
		$tagliststr =  '\'' . implode('\' , \'', $deletedTagList) . '\'';
		$t1list = POD::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
		if ($t1list === null) 
			return; // What?
		// Flushing pageCache
		foreach($t1list as $tagids) {
			CacheControl::flushTag($tagids);
		}
		// Make string
		$t1liststr = implode(', ', $t1list);
		$taglist = POD::queryColumn(
			"SELECT tag FROM {$database['prefix']}TagRelations 
			 WHERE blogid = $blogid AND entry = $entry AND tag in ( $t1liststr )");
		if ($taglist === null) 
			return; // What?
		
		// now delete tag
		$tagliststr = implode(', ', $taglist);
	
	// step 5. Delete Relation
		POD::execute("DELETE FROM {$database['prefix']}TagRelations WHERE blogid = $blogid AND entry = $entry AND tag in ( $tagliststr )");
	
	// step 6. Delete Tag
		$nottargets = POD::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE tag in ( $tagliststr )");
		if (count($nottargets) > 0) {
			$nottargetstr	= implode(', ', $nottargets);
			POD::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) AND id NOT IN ( $nottargetstr )");
		} else {
				POD::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr )");
		}

	}
}

function deleteTagsWithEntryId($blogid, $entry)
{
	global $database;
	$taglist = POD::queryColumn("SELECT tag 
		FROM {$database['prefix']}TagRelations 
		WHERE blogid = $blogid 
			AND entry = $entry");
	if ($taglist !== null) {
		$tagliststr = implode(',', $taglist);
		
		foreach($taglist as $tagid) {
			CacheControl::flushTag($tagid);
		}
		POD::execute("DELETE FROM {$database['prefix']}TagRelations 
			WHERE blogid = $blogid 
				AND entry = $entry");
		$nottargets = POD::queryColumn("SELECT DISTINCT tag 
			FROM {$database['prefix']}TagRelations 
			WHERE tag in ( $tagliststr )");
		if (count($nottargets) > 0) {
			$nottargetstr	= implode(', ', $nottargets);
			POD::execute("DELETE FROM {$database['prefix']}Tags 
				WHERE id IN ( $tagliststr ) 
					AND id NOT IN ( $nottargetstr )");
		} else {
			POD::execute("DELETE FROM {$database['prefix']}Tags 
				WHERE id IN ( $tagliststr )");
		}
	}
}

function deleteTagById($blogid, $id) {
	global $database;

	$result = POD::execute('DELETE FROM '.$database['prefix'].'TagRelations WHERE blogid = '.$blogid.' AND tag = '.$id);
	if (!$result) {
		return false;
	}

	$count = POD::queryCell('SELECT COUNT(*) FROM '.$database['prefix'].'TagRelations WHERE tag = '.$id);
	if (intval($count) > 0) {
		POD::execute('DELETE FROM '.$database['prefix'].'Tags WHERE id = '.$id);
	}

	return true;
}
?>
