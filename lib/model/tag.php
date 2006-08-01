<?

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



function removeEmptyTagHelper($var)
{
	return (strlen($var) > 0);
}

function addTagsWithEntryId($owner, $entry, /*string array*/$taglist)
{
	global $database;
	if ($taglist == null)
		return;

	$tmptaglist = array_filter($taglist, 'removeEmptyTagHelper');
	$taglist = array();
	foreach($tmptaglist as $tag) {
		$tag = mysql_real_escape_string(trim($tag));
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
							(SELECT $owner, t.id, $entry FROM {$database['prefix']}Tags as t 
									WHERE 
										name in ( $tagliststr ) AND  
										t.id NOT IN 
											( SELECT tag FROM {$database['prefix']}TagRelations WHERE 
												(tag = t.id) AND (entry = $entry) AND (owner = $owner)
											)
							)");
	*/
	// For MySQL 3, Simple Query Version
	$tagIDs = DBQuery::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
	$tagrelations = array();
	foreach($tagIDs as $tagid)
	{
		array_push($tagrelations, " ($owner, $tagid, $entry) ");
	}
	$tagRelationStr = implode(', ', $tagrelations);
	DBQuery::execute("INSERT IGNORE INTO {$database['prefix']}TagRelations VALUES $tagRelationStr");
}

function modifyTagsWithEntryId($owner, $entry, /*string array*/$taglist)
{
	global $database;
	if ($taglist == null)
		$taglist = array();
		
	$tmptaglist = array_filter($taglist, 'removeEmptyTagHelper');
	$taglist = array();
	foreach($tmptaglist as $tag) {
		$tag = mysql_real_escape_string(trim($tag));
		array_push($taglist, $tag);
	}
	
	// step 1. Get deleted Tag
	$toldlist = DBQuery::queryColumn("SELECT tag FROM {$database['prefix']}TagRelations WHERE owner = $owner AND entry = $entry");
	$tmpoldtaglist	 = null;
	if (count($toldlist) > 0) {
		$toldliststr = implode(', ', $toldlist);
		$tmpoldtaglist = DBQuery::queryColumn("SELECT name FROM {$database['prefix']}Tags WHERE id IN ( $toldliststr )");
	}
	if ($tmpoldtaglist == null)
		$tmpoldtaglist = array();
	$oldtaglist = array();
	foreach($tmpoldtaglist as $tag) {
		$tag = mysql_real_escape_string(trim($tag));
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
								(SELECT $owner, t.id, $entry FROM {$database['prefix']}Tags as t 
										WHERE 
											name in ( $tagliststr ) AND  
											t.id NOT IN 
												( SELECT tag FROM {$database['prefix']}TagRelations WHERE 
													(tag = t.id) AND (entry = $entry) AND (owner = $owner)
												)
								)");
		*/
		// For MySQL 3, Simple Query Version
		$tagIDs = DBQuery::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
		$tagrelations = array();
		foreach($tagIDs as $tagid)
		{
			array_push($tagrelations, " ($owner, $tagid, $entry) ");
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
		$t1liststr = implode(', ', $t1list);
		$taglist = DBQuery::queryColumn(
				"SELECT tag FROM {$database['prefix']}TagRelations 
						WHERE owner = $owner AND entry = $entry AND tag in ( $t1liststr )");
		if ($taglist == null) 
			return; // What?
		
		// now delete tag
		$tagliststr = implode(', ', $taglist);
	
	// step 5. Delete Relation
		DBQuery::execute("DELETE FROM {$database['prefix']}TagRelations WHERE owner = $owner AND entry = $entry AND tag in ( $tagliststr )");
	
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

function deleteTagsWithEntryId($owner, $entry)
{
	global $database;
	$taglist = DBQuery::queryColumn("SELECT tag FROM {$database['prefix']}TagRelations WHERE owner = $owner AND entry = $entry");
	if ($taglist != null) {
		$tagliststr = implode(',', $taglist);
		
		DBQuery::execute("DELETE FROM {$database['prefix']}TagRelations WHERE owner = $owner AND entry = $entry");
		$nottargets = DBQuery::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE tag in ( $tagliststr )");
		if (count($nottargets) > 0) {
			$nottargetstr	= implode(', ', $nottargets);
			DBQuery::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) AND id NOT IN ( $nottargetstr )");
		} else {
			DBQuery::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr )");
		}
	}
}
?>