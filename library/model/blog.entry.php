<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getEntriesTotalCount($blogid) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0'.getPrivateCategoryExclusionQuery($blogid);
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$count =  POD::queryCell("SELECT COUNT(id) 
		FROM {$database['prefix']}Entries e
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0");
	return ($count ? $count : 0);
}

function getNoticesTotalCount($blogid) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	return POD::queryCell("SELECT COUNT(*) 
		FROM {$database['prefix']}Entries e
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category = -2");
}

function getEntries($blogid, $attributes = '*', $condition = false, $order = 'published DESC') {
	global $database;
	if (!empty($condition))
		$condition = 'AND ' . $condition;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (userid = '.getUserId().' OR visibility > 0)' : '';
	return POD::queryAll("SELECT $attributes FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 $visibility $condition ORDER BY $order");
}


function getTemplates($blogid, $attributes = '*', $condition = false, $order = 'published DESC') {
	global $database;
	if (!empty($condition))
		$condition = 'AND ' . $condition;
	return POD::queryAll("SELECT $attributes 
			FROM {$database['prefix']}Entries 
			WHERE blogid = $blogid 
				AND draft = 0 AND category = -4 $condition 
				ORDER BY $order");
}

function getEntry($blogid, $id, $draft = false) {
	global $database;
	requireModel('blog.attachment');
	if($id == 0) {
		if (!doesHaveOwnership())
			return null;
		deleteAttachments($blogid, 0);
		return array('id'    => 0,
				'userid'     => 0,
				'draft'      => 0,
				'visibility' => 0,
				'starred'    => 1,
				'category'   => 0,
				'location'   => '',
				'latitude'   => null,
				'longitude'  => null,
				'title'      => '',
				'content'    => '',
				'contentformatter' => getDefaultFormatter(),
				'contenteditor'    => getDefaultEditor(),
				'acceptcomment'    => 1,
				'accepttrackback'  => 1,
				'published'  => time(),
				'slogan'     => '');
	}
	if ($draft) {
		$entry = POD::queryRow("SELECT * FROM {$database['prefix']}Entries 
				WHERE blogid = $blogid 
					AND id = $id 
					AND draft = 1");
		if (!$entry)
			return null;
		if ($entry['published'] == 1)
			$entry['republish'] = true;
		else if ($entry['published'] != 0)
			$entry['appointed'] = $entry['published'];
		if ($id != 0)
			$entry['published'] = POD::queryCell("SELECT published 
					FROM {$database['prefix']}Entries 
					WHERE blogid = $blogid AND id = $id AND draft = 0");
		return $entry;
	} else {
		$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
		$entry = POD::queryRow("SELECT * 
				FROM {$database['prefix']}Entries 
				WHERE blogid = $blogid AND id = $id AND draft = 0 $visibility");
		if (!$entry)
			return null;
		if ($entry['visibility'] < 0)
			$entry['appointed'] = $entry['published'];
		return $entry;
	}
}

function getUserIdOfEntry($blogid, $id, $draft = false) {
	global $database;
	$result = POD::queryCell("SELECT userid 
		FROM {$database['prefix']}Entries
		WHERE 
			blogid = $blogid AND id = $id");
	if(!empty($result)) return $result;
	else return null;
}

function getEntryAttributes($blogid, $id, $attributeNames) {
	global $database;
	
	if (stristr($attributeNames, "from") != false) // security check!
		return null;
	
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$attributes = POD::queryRow("SELECT $attributeNames FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $id AND draft = 0 $visibility");
	return $attributes;
}

function getEntryListWithPagingByCategory($blogid, $category, $page, $count) {
	global $database, $suri, $folderURL, $blog;
	if ($category === null)
		return array();
	if (!doesHaveOwnership() && getCategoryVisibility($blogid, $category) < 2 && $category != 0)
		return array();
	if ($category > 0) {
		$categories = POD::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent = $category");
		array_push($categories, $category);
		if(!doesHaveOwnership()) 
			$categories = array_diff($categories, getCategoryVisibilityList($blogid, 'private'));
		$cond = 'AND e.category IN (' . implode(', ', $categories) . ')';
		$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	} else {
		$cond = 'AND e.category >= 0';
		$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0'.getPrivateCategoryExclusionQuery($blogid);
	}
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';

	$sql = "SELECT e.blogid,e.userid,e.id,e.title,e.comments,e.slogan,e.published
			FROM {$database['prefix']}Entries e 
			WHERE e.blogid = $blogid AND e.draft = 0 $visibility $cond 
			ORDER BY e.published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/".((!getBlogSetting('useSloganOnCategory',true) && isset($suri['id'])) ? $suri['id'] : $suri['value']));
}

function getEntryListWithPagingByAuthor($blogid, $author, $page, $count) {
	global $database, $suri, $folderURL;
	if ($author === null)
		return array();
	$userid = User::getUserIdByName($author);
	if(empty($userid)) return array();
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0'.getPrivateCategoryExclusionQuery($blogid);
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';

	$sql = "SELECT e.blogid,e.userid,e.id,e.title,e.comments,e.slogan,e.published
			FROM {$database['prefix']}Entries e 
			WHERE e.blogid = $blogid AND e.userid = $userid AND e.draft = 0 $visibility 
			ORDER BY e.published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getEntryListWithPagingByTag($blogid, $tag, $page, $count) {
	global $database, $suri, $folderURL;
	if ($tag === null)
		return array(array(), array('url'=>'','prefix'=>'','postfix'=>''));	
	$tag = POD::escapeString($tag);
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0'.getPrivateCategoryExclusionQuery($blogid);
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$sql = "SELECT e.blogid, e.userid, e.id, e.title, e.comments, e.slogan, e.published
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}TagRelations t ON e.id = t.entry AND e.blogid = t.blogid 
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 AND t.tag = '$tag' 
		ORDER BY published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/".((!getBlogSetting('useSloganOnTag',true) && isset($suri['id'])) ? $suri['id'] : $suri['value']));
}

function getEntryListWithPagingByPeriod($blogid, $period, $page, $count) {
	global $database, $suri, $folderURL;
	$cond = "AND e.published >= " . getTimeFromPeriod($period) . " AND e.published < " . getTimeFromPeriod(addPeriod($period));
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0'.getPrivateCategoryExclusionQuery($blogid);
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$sql = "SELECT e.blogid, e.userid, e.id, e.title, e.comments, e.slogan, e.published
		FROM {$database['prefix']}Entries e
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 $cond 
		ORDER BY e.published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getEntryListWithPagingBySearch($blogid, $search, $page, $count) {
	global $database, $suri, $folderURL;
	$search = escapeSearchString($search);
	$cond = strlen($search) == 0 ? 'AND 0' : "AND (title LIKE '%$search%' OR content LIKE '%$search%')";
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 1'.getPrivateCategoryExclusionQuery($blogid);
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$sql = "SELECT e.blogid, e.userid, e.id, e.title, e.comments, e.slogan, e.published
		FROM {$database['prefix']}Entries e
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 $cond
		ORDER BY e.published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getEntriesWithPaging($blogid, $page, $count) {
	global $database;
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0 AND (c.visibility > 1 OR e.category = 0)';
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$sql = "SELECT e.*, c.label AS categoryLabel 
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 
		ORDER BY e.published DESC";
	return Paging::fetch($sql, $page, $count);
}

function getEntriesWithPagingByCategory($blogid, $category, $page, $count, $countItem) {
	global $database, $folderURL, $suri;
	if ($category === null)
		return Paging::fetch(null, $page, $count, "$folderURL/{$suri['value']}");
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 1';
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	if ($category > 0) {
		$categories = POD::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent = $category $visibility");
		array_push($categories, $category);
		$cond = 'AND e.category IN (' . implode(', ', $categories) . ')';
		$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	} else {
		$cond = 'AND e.category >= 0';
		$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0 AND (c.visibility > 1 OR e.category = 0)';
	}
	$sql = "SELECT e.*, c.label AS categoryLabel 
		FROM {$database['prefix']}Entries AS e 
		LEFT JOIN {$database['prefix']}Categories c ON e.category = c.id AND e.blogid = c.blogid 
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility $cond 
		ORDER BY e.published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/".((!getBlogSetting('useSloganOnCategory',true) && isset($suri['id'])) ? $suri['id'] : $suri['value']),"?page=",$countItem);
}

function getEntriesWithPagingByTag($blogid, $tag, $page, $count, $countItem = null) {
	global $database, $folderURL, $suri;
	if ($tag === null)
		return Paging::fetch(null, $page, $count, "$folderURL/{$suri['value']}");
	$tag = POD::escapeString($tag);
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0'.getPrivateCategoryExclusionQuery($blogid);
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$sql = "SELECT e.*, c.label AS categoryLabel 
		FROM {$database['prefix']}Entries e
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		LEFT JOIN {$database['prefix']}TagRelations t ON e.id = t.entry AND e.blogid = t.blogid 
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 AND t.tag = '$tag' 
		ORDER BY e.published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/".((!getBlogSetting('useSloganOnTag',true) && isset($suri['id'])) ? $suri['id'] : $suri['value']),"?page=", $countItem);
}

function getEntriesWithPagingByNotice($blogid, $page, $count, $countItem = null) {
	global $database, $folderURL, $suri;
	$visibility = doesHaveOwnership() ? '' : 'AND visibility = 2';
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$sql = "SELECT * 
		FROM {$database['prefix']}Entries 
		WHERE blogid = $blogid $visibility AND category = -2 
		ORDER BY published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/{$suri['value']}","?page=", $countItem);
}

function getEntriesWithPagingByPeriod($blogid, $period, $page, $count) {
	global $database, $folderURL, $suri;
	$cond = "AND published >= " . getTimeFromPeriod($period) . " AND published < " . getTimeFromPeriod(addPeriod($period));
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0 AND (c.visibility > 1 OR e.category = 0)';
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$sql = "SELECT e.*, c.label AS categoryLabel 
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 $cond 
		ORDER BY e.published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getEntriesWithPagingBySearch($blogid, $search, $page, $count, $countItem) {
	global $database, $folderURL, $suri;
	$search = escapeSearchString($search);
	$cond = strlen($search) == 0 ? 'AND 0' : "AND (e.title LIKE '%$search%' OR e.content LIKE '%$search%')";
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 1 AND (c.visibility > 1 OR e.category = 0)';
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$sql = "SELECT e.*, c.label AS categoryLabel 
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 $cond 
		ORDER BY e.published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/{$suri['value']}","?page=", $countItem);
}

function getEntriesWithPagingByAuthor($blogid, $author, $page, $count, $countItem = null) {
	global $database, $folderURL, $suri;
	$userid = User::getUserIdByName($author);
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0 AND (c.visibility > 1 OR e.category = 0)';
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$sql = "SELECT e.*, c.label categoryLabel 
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		WHERE e.blogid = $blogid AND e.userid = $userid AND e.draft = 0 $visibility AND e.category >= 0 
		ORDER BY e.published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/{$suri['value']}","?page=", $countItem);
}

function getEntriesWithPagingForOwner($blogid, $category, $search, $page, $count, $visibility = null, $starred = null, $draft = null, $tag = null) {
	global $database, $suri;
	
	$teamMemberFilter = "";
	if( ! Acl::check("group.editors", "entry.list") ) {
		$teamMemberFilter = " AND e.userid = ".getUserId();
	}
	
	$sqlTable = "SELECT e.*, c.label AS categoryLabel, d.id AS draft 
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c ON e.category = c.id AND e.blogid = c.blogid 
		LEFT JOIN {$database['prefix']}Entries d ON e.blogid = d.blogid AND e.id = d.id AND d.draft = 1 ";
	$sql = " WHERE e.blogid = $blogid AND e.draft = 0" . $teamMemberFilter;
	if ($category > 0) {
		$categories = POD::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
	} else if ($category == -3) {
		$sql .= ' AND e.category = 0';
	} else if ($category == -5) {
		$sql .= ' AND e.category >= -2';
	} else if ($category == 0) {
		$sql .= ' AND e.category >= 0';
	} else {
		$sql .= ' AND e.category = '.$category;
	}
	if(isset($visibility)) {
		if(Validator::isInteger($visibility,0,3)) {
			$sql .= ' AND e.visibility = '.$visibility;
		} else {
			$sql .= ' AND e.visibility '.$visibility;
		}
	}
	if(isset($starred)) {
		if(Validator::isInteger($starred,0,3)) {
			$sql .= ' AND e.starred = '.$starred;
		} else {
			$sql .= ' AND e.starred '.$starred;
		}
	}
	if (!empty($search)) {
		$search = escapeSearchString($search);
		$sql .= " AND (e.title LIKE '%$search%' OR e.content LIKE '%$search%')";
	}
	if (!empty($tag)) {
		$sqlTable .= " LEFT JOIN {$database['prefix']}TagRelations t ON e.id = t.entry AND e.blogid = t.blogid ";
		$sql .= ' AND t.tag = '.$tag;
	}

	$sql .= ' ORDER BY e.published DESC';
	return Paging::fetch($sqlTable.$sql, $page, $count);
}

function getEntryWithPaging($blogid, $id, $isNotice = false, $categoryId = false) {
	global $database, $folderURL;
	requireModel('blog.category');
	$entries = array();
	$paging = Paging::init($folderURL, '/');
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$visibility .= ($isNotice || doesHaveOwnership())  ? '' : ' AND (c.visibility > 1 OR e.category = 0)';
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$category = $isNotice ? 'e.category = -2' : 'e.category >= 0';
	if($categoryId !== false) {
		if($categoryId != 0) {	// Not a 'total' category.
			$childCategories = getChildCategoryId($blogid, $categoryId);
			if(!empty($childCategories)) {
				$category = 'e.category IN ('.$categoryId.','.implode(",",$childCategories).')';
			} else {
				$category = 'e.category = '.$categoryId;
			}
		}
	}
	$currentEntry = POD::queryRow("SELECT e.*, c.label AS categoryLabel 
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		WHERE e.blogid = $blogid 
			AND e.id = $id 
			AND e.draft = 0 $visibility AND $category");
	$result = POD::query("SELECT e.id 
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		WHERE e.blogid = $blogid 
			AND e.draft = 0 $visibility AND $category 
		ORDER BY e.published DESC");
	if (!$result || !$currentEntry)
		return array($entries, $paging);
	if($categoryId !== false) {
		$paging['pages'] = $categoryId == 0 ? getEntriesTotalCount($blogid):getEntriesCountByCategory($blogid, $categoryId);
		$paging['postfix'] = '?category='.$categoryId;
	} else {
		$paging['pages'] = ($isNotice) ? getNoticesTotalCount($blogid) : getEntriesTotalCount($blogid);
	}

	for ($i = 1; $entry = POD::fetch($result); $i++) {
		if ($entry['id'] != $id) {
			if (array_push($paging['before'], $entry['id']) > 4) {
				if ($i == 5) 
					$paging['first'] = array_shift($paging['before']);
				else 
					array_shift($paging['before']);
			}
			continue;
		}
		$paging['page'] = $i;
		array_push($entries, $currentEntry);
		$paging['after'] = array();
		for ($i++; (count($paging['after']) < 4) && ($entry = POD::fetch($result)); $i++)
			array_push($paging['after'], $entry['id']);
		if ($i < $paging['pages']) {
			while ($entry = POD::fetch($result))
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

function getEntryWithPagingBySlogan($blogid, $slogan, $isNotice = false, $categoryId = false) {
	global $database;
	global $blogURL;
	requireModel('blog.category');
	$entries = array();
	$paging = $isNotice ? Paging::init("$blogURL/notice", '/') : Paging::init("$blogURL/entry", '/');
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$visibility .= ($isNotice || doesHaveOwnership()) ? '' : getPrivateCategoryExclusionQuery($blogid);
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$category = $isNotice ? 'e.category = -2' : 'e.category >= 0';
	if($categoryId !== false) {
		if(!$categoryId == 0) {	// Not a 'total' category.
			$childCategories = getChildCategoryId($blogid, $categoryId);
			if(!empty($childCategories)) {
				$category = 'e.category IN ('.$categoryId.','.implode(",",$childCategories).')';
			} else {
				$category = 'e.category = '.$categoryId;
			}
		}
	}
	$currentEntry = POD::queryRow("SELECT e.*, c.label AS categoryLabel 
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		WHERE e.blogid = $blogid 
			AND e.slogan = '".POD::escapeString($slogan)."' 
			AND e.draft = 0 $visibility AND $category");

	$result = POD::query("SELECT e.id, e.slogan 
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		WHERE e.blogid = $blogid 
			AND e.draft = 0 $visibility AND $category 
		ORDER BY e.published DESC");
	if (!$result || !$currentEntry)
		return array($entries, $paging);
	
	if($categoryId !== false) {
		$paging['pages'] = $categoryId == 0 ? getEntriesTotalCount($blogid):getEntriesCountByCategory($blogid, $categoryId);
		$paging['postfix'] = '?category='.$categoryId;
	} else {
		$paging['pages'] = ($isNotice) ? getNoticesTotalCount($blogid) : getEntriesTotalCount($blogid);
	}
	
	for ($i = 1; $entry = POD::fetch($result); $i++) {
		if ($entry['slogan'] != $slogan) {
			if (array_push($paging['before'], $entry['slogan']) > 4) if ($i == 5)
				$paging['first'] = array_shift($paging['before']);
			else
				array_shift($paging['before']);
			continue;
		}
		$paging['page'] = $i;
		array_push($entries, $currentEntry);
		$paging['after'] = array();
		for ($i++; (count($paging['after']) < 4) && ($entry = POD::fetch($result)); $i++)
			array_push($paging['after'], $entry['slogan']);
		if ($i < $paging['pages']) {
			while ($entry = POD::fetch($result))
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
	$slogan = preg_replace('/-+/', ' ', $slogan);
	$slogan = preg_replace('@[!-/:-\@\[-\^`{-~]+@', '', $slogan);
	$slogan = preg_replace('/\s+/', '-', $slogan);
	$slogan = trim($slogan, '-');
	return strlen($slogan) > 0 ? $slogan : 'XFile';
}

function getRecentEntries($blogid) {
	global $database, $skinSetting;
	$entries = array();
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0'.getPrivateCategoryExclusionQuery($blogid);
	$result = POD::query("SELECT e.id, e.userid, e.title, e.slogan, e.comments, e.published 
		FROM {$database['prefix']}Entries e
		WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 
		ORDER BY published DESC LIMIT {$skinSetting['entriesOnRecent']}");
	while ($entry = POD::fetch($result)) {
		array_push($entries, $entry);
	}
	return $entries;
}

function addEntry($blogid, $entry, $userid = null) {
	global $database, $blog, $gCacheStorage;
	requireModel("blog.attachment");
	requireModel("blog.feed");
	requireModel("blog.category");
	requireModel("blog.tag");
	requireModel("blog.locative");
	requireComponent('Textcube.Data.Tag');

	if(empty($userid)) $entry['userid'] = getUserId();
	else $entry['userid'] = $userid;
	$entry['title'] = UTF8::lessenAsEncoding(trim($entry['title']), 255);
	$entry['location'] = UTF8::lessenAsEncoding(trim($entry['location']), 255);
	$entry['slogan'] = array_key_exists('slogan', $entry) ? trim($entry['slogan']) : '';
	
	if((empty($entry['slogan']))||($entry['category'] == -1)) {
		$slogan = $slogan0 = getSlogan($entry['title']);
	} else {
		$slogan = $slogan0 = getSlogan($entry['slogan']);
	}

	$slogan = POD::escapeString(UTF8::lessenAsEncoding($slogan, 255));
	$title = POD::escapeString($entry['title']);

	if($entry['category'] == -1) {
		if($entry['visibility'] == 1 || $entry['visibility'] == 3)
			return false;
		if(POD::queryCell("SELECT count(*) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 AND title = '$title' AND category = -1") > 0)
			return false;
	}
	
	if ($entry['category'] < 0) {
		if ($entry['visibility'] == 1) $entry['visibility'] = 0;
		if ($entry['visibility'] == 3) $entry['visibility'] = 2;
	}
	if ($entry['category'] == -4) {
		$entry['visibility'] = 0;
	}

	$result = POD::queryCount("SELECT slogan FROM {$database['prefix']}Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
	for ($i = 1; $result > 0; $i++) {
		if ($i > 1000)
			return false;
		$slogan = POD::escapeString(UTF8::lessenAsEncoding($slogan0, 245) . '-' . $i);
		$result = POD::queryCount("SELECT slogan FROM {$database['prefix']}Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
	}
	$userid = $entry['userid'];
	$content = POD::escapeString($entry['content']);
	$contentformatter = POD::escapeString($entry['contentformatter']);
	$contenteditor = POD::escapeString($entry['contenteditor']);
	$password = POD::escapeString(generatePassword());
	$location = POD::escapeString($entry['location']);
	$latitude = isset($entry['latitude']) && !is_null($entry['latitude']) ? $entry['latitude'] : 'NULL';
	$longitude = isset($entry['longitude']) && !is_null($entry['longitude']) ? $entry['longitude'] : 'NULL';
	if (!isset($entry['firstEntry']) && isset($entry['published']) && is_numeric($entry['published']) && ($entry['published'] >= 2)) {
		$published = $entry['published'];
		$entry['visibility'] = 0 - $entry['visibility'];
		if($entry['visibility'] < 0) {
			$closestReservedTime = Setting::getBlogSettingGlobal('closestReservedPostTime',INT_MAX);
			if($published < $closestReservedTime) {
				setBlogSetting('closestReservedPostTime',$published);
			}
		}
	} else {
		$published = 'UNIX_TIMESTAMP()';
	}
	
	$currentMaxId = POD::queryCell("SELECT MAX(id) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0");
	if(!empty($currentMaxId) && $currentMaxId > 0) {
		$id = $currentMaxId + 1;
	} else {
		$id = 1;
	}
	$result = POD::query("INSERT INTO {$database['prefix']}Entries 
			(blogid, userid, id, draft, visibility, starred, category, title, slogan, content, contentformatter,
			 contenteditor, location, latitude, longitude, password, acceptcomment, accepttrackback, published, created, modified,
			 comments, trackbacks, pingbacks) 
			VALUES (
			$blogid,
			$userid,
			$id,
			0,
			{$entry['visibility']},
			{$entry['starred']},
			{$entry['category']},
			'$title',
			'$slogan',
			'$content',
			'$contentformatter',
			'$contenteditor',
			'$location',
			$latitude,
			$longitude,
			'$password',
			{$entry['acceptcomment']},
			{$entry['accepttrackback']},
			$published,
			UNIX_TIMESTAMP(),
			UNIX_TIMESTAMP(),
			0,
			0,
			0)");
	if (!$result)
		return false;
	POD::query("UPDATE {$database['prefix']}Attachments SET parent = $id WHERE blogid = $blogid AND parent = 0");
	POD::query("DELETE FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $id AND draft = 1");
	updateCategoryByEntryId($blogid, $id, 'add');

	if ($entry['visibility'] == 3)
		syndicateEntry($id, 'create');
	if ($entry['visibility'] >= 2) {
		CacheControl::flushAuthor($userid);
		CacheControl::flushDBCache('entry');
		$gCacheStorage->purge();
		clearFeed();
	}
	if (!empty($entry['tag'])) {
		$tags = getTagsWithEntryString($entry['tag']);
		Tag::addTagsWithEntryId($blogid, $id, $tags);
	}
	return $id;
}

function updateEntry($blogid, $entry, $updateDraft = 0) {
	global $database, $blog, $gCacheStorage;
	requireModel('blog.tag');
	requireModel('blog.locative');
	requireModel('blog.attachment');
	requireModel('blog.category');
	requireModel('blog.feed');
	requireComponent('Textcube.Data.Tag');

	if($entry['id'] == 0) return false;
	
	$oldEntry = POD::queryRow("SELECT *
		FROM {$database['prefix']}Entries
		WHERE blogid = $blogid
		AND id = {$entry['id']}
		AND draft = 0");
	if(empty($oldEntry)) return false;

	if(empty($entry['userid'])) $entry['userid'] = getUserId(); 
	$entry['title'] = UTF8::lessenAsEncoding(trim($entry['title']));
	$entry['location'] = UTF8::lessenAsEncoding(trim($entry['location']));
	$entry['slogan'] = array_key_exists('slogan', $entry) ? trim($entry['slogan']) : '';
	if(empty($entry['slogan'])) {
		$slogan = $slogan0 = getSlogan($entry['title']);
	} else {
		$slogan = $slogan0 = getSlogan($entry['slogan']);
	}
	$slogan = POD::escapeString(UTF8::lessenAsEncoding($slogan, 255));
	$title = POD::escapeString($entry['title']);

	if($entry['category'] == -1) {
		if($entry['visibility'] == 1 || $entry['visibility'] == 3)
			return false;
		if(POD::queryCell("SELECT count(*) 
			FROM {$database['prefix']}Entries 
			WHERE blogid = $blogid 
				AND id <> {$entry['id']} 
				AND draft = 0 
				AND title = '$title' 
				AND category = -1") > 0)
			return false;
	}

	if ($entry['category'] < 0) {
		if ($entry['visibility'] == 1) $entry['visibility'] = 0;
		if ($entry['visibility'] == 3) $entry['visibility'] = 2;
	}
	if ($entry['category'] == -4) {
		$entry['visibility'] = 0;
	}
	
	$result = POD::queryCount("SELECT slogan 
		FROM {$database['prefix']}Entries 
		WHERE blogid = $blogid 
		AND slogan = '$slogan' 
		AND id = {$entry['id']} 
		AND draft = 0
		LIMIT 1");
	if ($result == 0) { // if changed
		$result = POD::queryCount("SELECT slogan FROM {$database['prefix']}Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
		for ($i = 1; $result > 0; $i++) {
			if ($i > 1000)
				return false;
			$slogan = POD::escapeString(UTF8::lessenAsEncoding($slogan0, 245) . '-' . $i);
			$result = POD::queryCount("SELECT slogan FROM {$database['prefix']}Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
		}
	}
	$tags = getTagsWithEntryString($entry['tag']);
	Tag::modifyTagsWithEntryId($blogid, $entry['id'], $tags);
	
	$location = POD::escapeString($entry['location']);
	$latitude = isset($entry['latitude']) && !is_null($entry['latitude']) ? $entry['latitude'] : 'NULL';
	$longitude = isset($entry['longitude']) && !is_null($entry['longitude']) ? $entry['longitude'] : 'NULL';
	$content = POD::escapeString($entry['content']);
	$contentformatter = POD::escapeString($entry['contentformatter']);
	$contenteditor = POD::escapeString($entry['contenteditor']);
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
			if($entry['visibility'] < 0) {
				$closestReservedTime = Setting::getBlogSettingGlobal('closestReservedPostTime',9999999999);
				if($published < $closestReservedTime) {
					setBlogSetting('closestReservedPostTime',$published);
				}
			}
			break;
	}

	$result = POD::query("UPDATE {$database['prefix']}Entries
			SET
				userid             = {$entry['userid']},
				visibility         = {$entry['visibility']},
				starred            = {$entry['starred']},
				category           = {$entry['category']},
				draft              = 0,
				location           = '$location',
				latitude           = $latitude,
				longitude          = $longitude,
				title              = '$title',
				content            = '$content',
				contentformatter   = '$contentformatter',
				contenteditor      = '$contenteditor',
				slogan             = '$slogan',
				acceptcomment      = {$entry['acceptcomment']},
				accepttrackback    = {$entry['accepttrackback']},
				published          = $published,
				modified           = UNIX_TIMESTAMP()
			WHERE blogid = $blogid AND id = {$entry['id']} AND draft = $updateDraft");
	if ($result)
		@POD::query("DELETE FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = {$entry['id']} AND draft = 1");

	updateCategoryByEntryId($blogid, $entry['id'], 'update', 
		array('category'=>array($oldEntry['category'],$entry['category']),
			'visibility'=>array($oldEntry['visibility'],$entry['visibility'])
		));	

	CacheControl::flushAuthor($entry['userid']);	
	CacheControl::flushDBCache('entry');
	$gCacheStorage->purge();
	if ($entry['visibility'] == 3)
		syndicateEntry($entry['id'], 'modify');
	POD::query("UPDATE {$database['prefix']}Attachments SET parent = {$entry['id']} WHERE blogid = $blogid AND parent = 0");
	if ($entry['visibility'] >= 2)
		clearFeed();
	return $result ? $entry['id'] : false;
}

function saveDraftEntry($blogid, $entry) {
	global $database, $blog;
	requireModel('blog.tag');
	requireModel('blog.locative');
	requireModel('blog.attachment');
	requireModel('blog.category');
	requireModel('blog.feed');
	requireComponent('Textcube.Data.Tag');

	if($entry['id'] == 0) return -11;

	$draftCount = POD::queryCell("SELECT count(*) FROM {$database['prefix']}Entries
		WHERE blogid = $blogid
			AND id = ".$entry['id']."
			AND draft = 1");

	if($draftCount > 0) { // draft가 없으면 insert를, 있으면 update를.
		$doUpdate = true;
	} else {
		$doUpdate = false;
	}
	// 원 글을 읽어서 몇가지 정보를 보존한다. 원래 글이 없는 경우 draft는 저장될 수 없다.
	$origEntry = POD::queryRow("SELECT created, comments, trackbacks, pingbacks, password
		FROM {$database['prefix']}Entries
		WHERE blogid = $blogid
			AND id = ".$entry['id']."
			AND draft = 0");
	if(empty($origEntry)) return -12;
	
	$created = $origEntry['created'];
	$comments = $origEntry['comments'];
	$trackbacks = $origEntry['trackbacks'];
	$pingbacks = $origEntry['pingbacks'];
	$password = $origEntry['password'];
	
	if(empty($entry['userid'])) $entry['userid'] = getUserId(); 
	$entry['title'] = UTF8::lessenAsEncoding(trim($entry['title']));
	$entry['location'] = UTF8::lessenAsEncoding(trim($entry['location']));
	$entry['slogan'] = array_key_exists('slogan', $entry) ? trim($entry['slogan']) : '';
	if(empty($entry['slogan'])) {
		$slogan = $slogan0 = getSlogan($entry['title']);
	} else {
		$slogan = $slogan0 = getSlogan($entry['slogan']);
	}
	$slogan = POD::escapeString(UTF8::lessenAsEncoding($slogan, 255));
	$title = POD::escapeString($entry['title']);

	if($entry['category'] == -1) {
		if($entry['visibility'] == 1 || $entry['visibility'] == 3)
			return false;
		if(POD::queryCell("SELECT count(*) 
			FROM {$database['prefix']}Entries 
			WHERE blogid = $blogid 
				AND id <> {$entry['id']} 
				AND draft = 0 
				AND title = '$title' 
				AND category = -1") > 0)
			return -13;
	}

	if ($entry['category'] < 0) {
		if ($entry['visibility'] == 1) $entry['visibility'] = 0;
		if ($entry['visibility'] == 3) $entry['visibility'] = 2;
	}
	if ($entry['category'] == -4) {
		$entry['visibility'] = 0;
	}
	
	$result = POD::queryCount("SELECT slogan 
		FROM {$database['prefix']}Entries 
		WHERE blogid = $blogid 
		AND slogan = '$slogan' 
		AND id = {$entry['id']} 
		AND draft = 0 LIMIT 1");
	if ($result == 0) { // if changed
		$result = POD::queryExistence("SELECT slogan FROM {$database['prefix']}Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
		for ($i = 1; $result != false; $i++) {
			if ($i > 1000)
				return false;
			$slogan = POD::escapeString(UTF8::lessenAsEncoding($slogan0, 245) . '-' . $i);
			$result = POD::queryExistence("SELECT slogan FROM {$database['prefix']}Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
		}
	}
	$tags = getTagsWithEntryString($entry['tag']);
	Tag::modifyTagsWithEntryId($blogid, $entry['id'], $tags);
	
	$location = POD::escapeString($entry['location']);
	$latitude = isset($entry['latitude']) && !is_null($entry['latitude']) ? $entry['latitude'] : 'NULL';
	$longitude = isset($entry['longitude']) && !is_null($entry['longitude']) ? $entry['longitude'] : 'NULL';
	$content = POD::escapeString($entry['content']);
	$contentformatter = POD::escapeString($entry['contentformatter']);
	$contenteditor = POD::escapeString($entry['contenteditor']);
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

	if($doUpdate) {
		$result = POD::query("UPDATE {$database['prefix']}Entries
			SET
				userid             = {$entry['userid']},
				visibility         = {$entry['visibility']},
				starred            = {$entry['starred']},
				category           = {$entry['category']},
				draft              = 1,
				location           = '$location',
				latitude           = $latitude,
				longitude          = $longitude,
				title              = '$title',
				content            = '$content',
				contentformatter   = '$contentformatter',
				contenteditor      = '$contenteditor',
				slogan             = '$slogan',
				acceptcomment      = {$entry['acceptcomment']},
				accepttrackback    = {$entry['accepttrackback']},
				published          = $published,
				modified           = UNIX_TIMESTAMP()
			WHERE blogid = $blogid AND id = {$entry['id']} AND draft = 1");
	} else {
		$result = POD::query("INSERT INTO {$database['prefix']}Entries 
			(blogid, userid, id, draft, visibility, starred, category, title, slogan, content, contentformatter,
			 contenteditor, location, password, acceptcomment, accepttrackback, published, created, modified,
			 comments, trackbacks, pingbacks) 
			VALUES (
			$blogid,
			{$entry['userid']},
			{$entry['id']},
			1,
			{$entry['visibility']},
			{$entry['starred']},
			{$entry['category']},
			'$title',
			'$slogan',
			'$content',
			'$contentformatter',
			'$contenteditor',
			'$location',
			'$password',
			{$entry['acceptcomment']},
			{$entry['accepttrackback']},
			$published,
			$created,
			UNIX_TIMESTAMP(),
			$comments,
			$trackbacks,
			$pingbacks)");
	}
	return $result ? $entry['id'] : false;
}

function updateRemoteResponsesOfEntry($blogid, $id) {
	global $database;
	$trackbacks = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND entry = $id AND isfiltered = 0 AND responsetype = 'trackback'");
	$pingbacks  = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND entry = $id AND isfiltered = 0 AND responsetype = 'pingback'");
	if ($trackbacks === null || $pingbacks === null)
		return false;
	return POD::execute("UPDATE {$database['prefix']}Entries SET trackbacks = $trackbacks, pingbacks = $pingbacks WHERE blogid = $blogid AND id = $id");
}

function deleteEntry($blogid, $id) {
	global $database, $gCacheStorage;
	requireModel("blog.feed");
	requireModel("blog.category");
	requireModel("blog.attachment");
	requireModel("blog.tag");
	requireComponent("Textcube.Data.Tag");

	$target = getEntry($blogid, $id);
	if (is_null($target)) return false;
	if (POD::queryCell("SELECT visibility FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $id") == 3)
		syndicateEntry($id, 'delete');
	CacheControl::flushEntry($id);
	CacheControl::flushDBCache('entry');
	CacheControl::flushDBCache('comment');
	CacheControl::flushDBCache('trackback');
	$gCacheStorage->purge();
	$result = POD::queryCount("DELETE FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $id");
	if ($result > 0) {
		$result = POD::query("DELETE FROM {$database['prefix']}Comments WHERE blogid = $blogid AND entry = $id");
		$result = POD::query("DELETE FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND entry = $id");
		$result = POD::query("DELETE FROM {$database['prefix']}RemoteResponseLogs WHERE blogid = $blogid AND entry = $id");
		updateCategoryByEntryId($blogid, $id, 'delete', array('entry' => $target));
		deleteAttachments($blogid, $id);
		
		Tag::deleteTagsWithEntryId($blogid, $id);
		clearFeed();
		fireEvent('DeletePost', $id, null);
		return true;
	}
	return false;
}

function changeCategoryOfEntries($blogid, $entries, $category) {
	global $database;
	requireModel("blog.category");
	requireModel("blog.feed");

	$targets = array_unique(preg_split('/,/', $entries, -1, PREG_SPLIT_NO_EMPTY));
	$effectedCategories = array();
	if ( count($targets)<1 || !is_numeric($category) ) 
		return false;
		
	if ($category == -1) { // Check Keyword duplication
		foreach($targets as $entryId) {
			$title = POD::queryCell("SELECT title FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $entryId AND draft = 0");
			if (is_null($title)) return false;
			if (POD::queryExistence("SELECT id FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id <> $entryId AND draft = 0 AND title = '$title' AND category = -1") == true) return false;
		}
	} else {
		$parent = getParentCategoryId($blogid, $categoryId);
		array_push($effectedCategories, $parent);
	}
	
	foreach($targets as $entryId) {
		list($effectedCategoryId, $oldVisibility) = POD::queryRow("SELECT category, visibility FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $entryId AND draft = 0");
		$visibility = 	$oldVisibility;
		if ($category < 0) {
			if ($visibility == 1) $visibility = 0;
			if ($visibility == 3) $visibility = 2;
		}
		
		if (($oldVisibility == 3) && ($visibility != 3))
			syndicateEntry($entryId, 'delete');
			
		POD::execute("UPDATE {$database['prefix']}Entries SET category = $category , visibility = $visibility WHERE blogid = $blogid AND id = $entryId");

		if (!in_array($effectedCategoryId, $effectedCategories)) {
			$parent = getParentCategoryId($blogid, $effectedCategoryId);
			array_push($effectedCategories, $parent);
		}
	}
	$effected = false;
	foreach($effectedCategories as $effectedCategory) {
		updateEntriesOfCategory($blogid, $effectedCategory);
		$effected = true;
	}	

	if(updateEntriesOfCategory($blogid, $category)) {
		if ($effected) {
			clearFeed();
			CacheControl::flushDBCache('comment');
			CacheControl::flushDBCache('trackback');
		}
		return true;	
	}
	return false;
}

function changeAuthorOfEntries($blogid, $entries, $userid) {
	global $database;
	requireModel("blog.feed");

	$targets = array_unique(preg_split('/,/', $entries, -1, PREG_SPLIT_NO_EMPTY));
	foreach($targets as $entryId) {
		POD::execute("UPDATE {$database['prefix']}Entries SET userid = $userid WHERE blogid = $blogid AND id = $entryId");
	}
	clearFeed();
	CacheControl::flushAuthor();
	return true;
}

function setEntryVisibility($id, $visibility) {
	global $database;
	requireModel("blog.feed");
	requireModel("blog.category");
	requireComponent('Needlworks.Cache.PageCache');
	$blogid = getBlogId();
	if (($visibility < 0) || ($visibility > 3))
		return false;
	list($oldVisibility, $category) = POD::queryRow("SELECT visibility, category FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $id AND draft = 0");

	if ($category < 0) {
		if ($visibility == 1) $visibility = 0;
		if ($visibility == 3) $visibility = 2;
	}
	
	if ($oldVisibility === null)
		return false;
	if ($visibility == $oldVisibility)
		return true;

	if ($oldVisibility == 3)
		syndicateEntry($id, 'delete');
	else if ($visibility == 3) {
		if (!syndicateEntry($id, 'create')) {
			POD::query("UPDATE {$database['prefix']}Entries 
				SET visibility = $oldVisibility, 
					modified = UNIX_TIMESTAMP() 
				WHERE blogid = $blogid AND id = $id");
			return false;
		}
	}

	$result = POD::queryCount("UPDATE {$database['prefix']}Entries 
		SET visibility = $visibility, 
			modified = UNIX_TIMESTAMP() 
		WHERE blogid = $blogid AND id = $id");
	if (!$result)		// Error.
		return false;
	if ($result == 0)	// Not changed.
		return true;

	if ($category >= 0) {
		if ((($oldVisibility >= 2) && ($visibility < 2)) || (($oldVisibility < 2) && ($visibility >= 2)))
			clearFeed();
		if ((($oldVisibility == 3) && ($visibility <= 2)) || (($oldVisibility <= 2) && ($visibility == 3)))
			clearFeed();
		if ($category > 0)
			updateCategoryByEntryId($blogid, $id, 'update',$parameters = array('visibility' => array($oldVisibility, $visibility)));
//			updateEntriesOfCategory($blogid, $category);
	}
	CacheControl::flushEntry($id);
	CacheControl::flushDBCache('entry');
	CacheControl::flushDBCache('comment');
	CacheControl::flushDBCache('trackback');
	fireEvent('ChangeVisibility', $visibility, $id);
	return true;
}

function protectEntry($id, $password) {
	global $database;
	$password = POD::escapeString($password);
	$result = POD::queryCount("UPDATE {$database['prefix']}Entries SET password = '$password', modified = UNIX_TIMESTAMP() WHERE blogid = ".getBlogId()." AND id = $id AND visibility = 1");
	if($result > 0) {
		CacheControl::flushEntry($id);
		CacheControl::flushDBCache('entry');
		CacheControl::flushDBCache('comment');
		CacheControl::flushDBCache('trackback');
		return true;
	} else return false;
}

function syndicateEntry($id, $mode) {
	$context = Model_Context::getInstance();
	$pool = DBModel::getInstance();
	
	$pool->reset('XMLRPCPingSettings');
	$pool->setQualifier('blogid','equals',$context->getProperty('blog.id'));
	$sites = $pool->getAll('url,type');
	
	$entry = getEntry($context->getProperty('blog.id'), $id);
	if (is_null($entry)) return false;
	
	if(!empty($sites)) {
		foreach ($sites as $site) {
			$rpc = new XMLRPC();
			$rpc->url = $site['url'];
			$result[$site['url']] = $rpc->call($context->getProperty('blog.title'), $context->getProperty('uri.default'));
		}
	}
	if($mode == 'create') {
		fireEvent('CreatePostSyndicate', $id, $entry);
	} else if($mode == 'modify') {
		fireEvent('ModifyPostSyndicate', $id, $entry);
	} else if($mode == 'delete') {
		fireEvent('DeletePostSyndicate', $id, $entry);
	}
	return true;
}

function publishEntries() {
	global $database;
	$blogid = getBlogId();
	$closestReservedTime = Setting::getBlogSettingGlobal('closestReservedPostTime',INT_MAX);
	if($closestReservedTime < Timestamp::getUNIXtime()) {
		$entries = POD::queryAll("SELECT id, visibility, category
			FROM {$database['prefix']}Entries 
			WHERE blogid = $blogid AND draft = 0 AND visibility < 0 AND published < UNIX_TIMESTAMP()");
		if (count($entries) == 0)
			return;
		foreach ($entries as $entry) {
			$result = POD::query("UPDATE {$database['prefix']}Entries 
				SET visibility = 0 
				WHERE blogid = $blogid AND id = {$entry['id']} AND draft = 0");
			if ($entry['visibility'] == -3) {
				if ($result && setEntryVisibility($entry['id'], 2)) {
					$updatedEntry = getEntry($blogid, $entry['id']);
					if (!is_null($updatedEntry)) {
						fireEvent('UpdatePost', $entry['id'], $updatedEntry);
						setEntryVisibility($entry['id'], 3);
					}
				}
			} else {
				if ($result) {
					setEntryVisibility($entry['id'], abs($entry['visibility']));
					$updatedEntry = getEntry($blogid, $entry['id']);
					if (!is_null($updatedEntry)) {
						fireEvent('UpdatePost', $entry['id'], $updatedEntry);
					}
				}
			}
		}
		$newClosestTime = POD::queryCell("SELECT min(published)
			FROM {$database['prefix']}Entries
			WHERE blogid = $blogid AND draft = 0 AND visibility < 0 AND published > UNIX_TIMESTAMP()");
		if(!empty($newClosestTime)) Setting::setBlogSettingGlobal('closestReservedPostTime',$newClosestTime);
		else Setting::setBlogSettingGlobal('closestReservedPostTime',INT_MAX);
	}
}

function getTagsWithEntryString($entryTag) {
	global $database;
	$tags = explode(',', $entryTag);
	
	$ret = array();
	
	foreach ($tags as $tag) {
		$tag = UTF8::lessenAsEncoding($tag, 255, '');
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

function getSloganById($blogid, $id) {
	global $database;
	$result = POD::queryCell("SELECT slogan FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $id");
	if (is_null($result))
		return false;
	else
		return $result;
}

function getEntryIdBySlogan($blogid, $slogan) {
	global $database;
	$result = POD::queryCell("SELECT id
		FROM {$database['prefix']}Entries 
		WHERE blogid = $blogid 
			AND slogan = '".POD::escapeString($slogan)."'");
	if(!$result) return false;
	else return $result;
}

function setEntryStar($entryId, $mark) {
	global $database;
	$result = POD::query("UPDATE {$database['prefix']}Entries
		SET starred = ".$mark."
		WHERE blogid = ".getBlogId()." AND id = ".$entryId);
	if(!$result) return false;
	else return true;
}

function getEntriesByTagId($blogid, $tagId) {
	global $database;

	return POD::queryAll('SELECT e.blogid, e.userid, e.id, e.title, e.comments, e.slogan, e.published FROM '.$database['prefix'].'Entries e LEFT JOIN '.$database['prefix'].'TagRelations t ON e.id = t.entry AND e.blogid = t.blogid WHERE e.blogid = '.$blogid.' AND t.tag = '.$tagId);
}
?>
