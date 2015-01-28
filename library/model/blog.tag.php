<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getTagId($blogid, $name) {
	global $database;
	$query = DBModel::getInstance();
	$query->reset('Tags');
	$query->setQualifier('name','equals',$name,true);
	return $query->getCell('id');
}

function getTagById($blogid, $id) {
	$query = DBModel::getInstance();
	$query->reset('Tags');
	$query->setQualifier('id','equals',$id);
	return $query->getCell('name');
}

function getTags($blogid, $entry) {
	$tags = array();
	$pool = DBModel::getInstance();
	$pool->init("Tags");
	$pool->setAlias("Tags","t");
	$pool->setAlias("TagRelations","r");
	$pool->join("TagRelations","inner",array(
		array("r.blogid","eq",$blogid),
		array("r.entry","eq",$entry),
		array("r.tag","eq","t.id")
	));
	if (!doesHaveOwnership()) {
		$pool->setAlias("Entries", "e");
		$pool->join("Entries", "inner", array(
			array("e.id", "eq", "r.entry"),
			array("e.visibility", ">", 0)
		));
	}
	$pool->setGroup("r.tag","t.id","t.name");
	$pool->setOrder("t.name","desc");
	$result = $pool->getAll("t.*");

	if(!empty($result)) {
		$tags = $result;
	}
	return $tags;
}

function getRandomTags($blogid) {
	global $database, $skinSetting;
	$tags = array();
	$aux = ($skinSetting['tagsOnTagbox'] == - 1) ? '' : "LIMIT {$skinSetting['tagsOnTagbox']}";
	if ($skinSetting['tagboxAlign'] == 1) { // order by count
		if (doesHaveOwnership())
			$tags = POD::queryAll("SELECT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t 
				INNER JOIN {$database['prefix']}TagRelations r ON r.blogid = $blogid AND r.tag = t.id
				GROUP BY r.tag, t.name, t.id 
				ORDER BY cnt DESC $aux");
		else
			$tags = POD::queryAll("SELECT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t
				INNER JOIN {$database['prefix']}TagRelations r ON t.id = r.tag AND r.blogid = $blogid
				INNER JOIN {$database['prefix']}Entries e ON r.entry = e.id AND e.visibility > 0 AND e.blogid = $blogid 
				GROUP BY r.tag, t.name, t.id
				ORDER BY cnt DESC $aux");
	} else if ($skinSetting['tagboxAlign'] == 2) {  // order by name
		if (doesHaveOwnership())
			$tags = POD::queryAll("SELECT DISTINCT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t, 
				INNER JOIN {$database['prefix']}TagRelations r ON t.id = r.tag AND r.blogid = $blogid 
				GROUP BY r.tag, t.name, t.id
				ORDER BY t.name $aux");
		else
			$tags = POD::queryAll("SELECT DISTINCT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t 
				INNER JOIN {$database['prefix']}TagRelations r ON t.id = r.tag AND r.blogid = $blogid
				INNER JOIN {$database['prefix']}Entries e ON r.entry = e.id AND e.visibility > 0 AND e.blogid = $blogid
				GROUP BY r.tag, t.name, t.id
				ORDER BY t.name $aux");
	} else { // random
		if (doesHaveOwnership())
			$tags = POD::queryAll("SELECT t.name, count(*) AS cnt, t.id FROM {$database['prefix']}Tags t
				INNER JOIN {$database['prefix']}TagRelations r ON t.id = r.tag AND r.blogid = $blogid
				GROUP BY r.tag, t.name, t.id 
				ORDER BY RAND() $aux");
		else
			$tags = POD::queryAll("SELECT t.name, count(*) as cnt, t.id FROM {$database['prefix']}Tags t
				INNER JOIN {$database['prefix']}TagRelations r ON t.id = r.tag AND r.blogid = $blogid
				INNER JOIN {$database['prefix']}Entries e ON r.entry = e.id AND e.visibility > 0 AND e.blogid = $blogid
				GROUP BY r.tag, t.name, t.id 
				ORDER BY RAND() $aux");
	}
	return $tags;
}

function getSiteTags($blogid) {
	$pool = DBModel::getInstance();
	$pool->init("Tags");
	$pool->setAlias("Tags","t");
	$pool->setAlias("TagRelations","r");
	$pool->join("TagRelations","inner",array(
		array("r.blogid","eq",$blogid),
		array("r.tag","eq","t.id")
	));

	if (!doesHaveOwnership()) {
		$pool->setAlias("Entries", "e");
		$pool->join("Entries", "inner", array(
			array("e.id", "eq", "r.entry"),
			array("e.visibility", ">", 0)
		));
	}
	$pool->setGroup("r.tag","t.id","t.name");
	$pool->setOrder("t.name","asc");
	$pool->setLimit(2000);
	$names = $pool->getAll("t.id, t.name");
	if(!empty($names)) return $names;
	else $names = array();
	return $names;
}

function getTagFrequencyRange() {
	$blogid = getBlogId();
	$max = $min = 0;
	$pool = DBModel::getInstance();
	$pool->init("TagRelations");
	$pool->setAlias("TagRelations","r");
	$pool->setQualifier("r.blogid","eq",$blogid);
	$pool->setGroup("r.tag");
	$pool->setOrder("cnt","desc");

	if(!doesHaveOwnership()) {
		$pool->setAlias("Entries", "e");
		$pool->join("Entries", "inner", array(
			array("r.blogid", "eq", "e.blogid"),
			array("e.visibility",">",0),
			array("r.entry", "eq", "e.id")
		));
	}
	$max = $pool->getCell("r.entry AS cnt");
	$max = ($max === null ? 0 : $max);
	$min = 1;
	return array($max, $min);
}

function getTagFrequency($tag, $max, $min) {
	$pool = DBModel::getInstance();

	$blogid = getBlogId();
	if (is_array($tag) && array_key_exists('cnt', $tag)) {
		$count = $tag['cnt'];
	} else {
		if (!is_array($tag)) {
			$tag = array('name' => $tag);
		}
		$pool->init("Tags");
		$pool->setAlias("Tags","t");
		$pool->setAlias("TagRelations","r");
		$pool->join("TagRelations","inner",array(
			array("r.tag","eq","t.id"),
			array("r.blogid","eq",$blogid)
		));
		$pool->setQualifier("t.name","eq",$tag['name'],true);

		if (!doesHaveOwnership()) {
			$pool->setAlias("Entries","e");
			$pool->join("Entries","inner",array(
				array("e.blogid","eq","r.blogid"),
				array("e.id","eq","r.entry"),
				array("e.visibility",">",0)
			));
		}
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
	$tags = array();
	$pool->init("Tags");
	$pool->setAlias("Tags","t");
	$pool->setAlias("TagRelations","r");
	$pool->join("TagRelations","inner",array(
		array("t.id","eq","r.tag"),
		array("r.blogid","eq",$blogid),

	));
	$pool->setGroup("tag","t.name");
	$pool->setOrder("cnt","desc");
	$pool->setLimit(10);
	if (!is_null($filter)) {
		$pool->setQualifier($filter[0],$filter[1],$filter[2],true);
	}
	$result = $pool->getAll("t.name, count(*) AS cnt",array("filter"=>"distinct"));
	if ($result) {
		foreach($result as $tag)
			array_push($tags, $tag[0]);
	}
	return $tags;
}

function deleteTagById($blogid, $id) {
	global $database;
	$pool = DBModel::getInstance();

	$pool->init("TagRelations");
	$pool->setQualifier("blogid","eq",$blogid);
	$pool->setQualifier("tag","eq",$id);

	/// delete relation
	$result = $pool->delete();

	if (!$result) {
		return false;
	}
	$pool->unsetQualifier("blogid");
	$count = $pool->getCount();
	if (intval($count) == 0) {
		$pool->init("Tags");
		$pool->setQualifier("id","eq",$id);
		$pool->delete();
	}

	return true;
}


function addTag($blogid, $name) {
	$tagId = getTagId($blogid,$name);
	if(empty($tagId)) {
		$query = DBModel::getInstance();
		$query->reset("Tags");
		$insertId = Tag::_getMaxId()+1;
		$query->setAttribute('id',$insertId);
		$query->setAttribute('name',$name,true);
		if($query->insert()) return $insertId;
		else return false;
	} else return $tagId;
}

function renameTag($blogid, $id, $name) {
	// 1. If tag with new name already exists, skip the tag creation process.
	// 2. If tag with new name does not exist in this service, create new tag.
	// 3. Modify the tag relation information
	// 4. If older tag is not used anymore, drop the tag.
	$oldTagId = $id;
	$newTagId = addTag($blogid, $name);
	$query = DBModel::getInstance();
	$query->reset("TagRelations");
	$query->setAttribute('tag',$newTagId);
	$query->setQualifier('blogid','equals',$blogid);
	$query->setQualifier('tag','equals',$oldTagId);
	$query->update();
	deleteTagById($blogid, $oldTagId);
	return $newTagId;
}
?>
