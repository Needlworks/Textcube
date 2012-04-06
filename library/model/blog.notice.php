<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getNoticesWithPaging($blogid, $search, $page, $count) {
	global $database, $folderURL, $suri;
	$aux = '';
	if (($search !== true) && $search) {
		$search = escapeSearchString($search);
		$aux = "AND (title LIKE '%$search%' OR content LIKE '%$search%')";
	}
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 1';
	$sql = "SELECT * FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 $visibility AND category = -2 $aux ORDER BY published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getNotice($blogid, $id) {
	$query = getDefaultDBModelOnNotice($blogid);
	$query->setQualifier('id','equals',$id);
	return $query->getAll('id, title, slogan, published, userid');
}

function getNotices($blogid) {
	$query = getDefaultDBModelOnNotice($blogid);
	return $query->getAll('id, title, slogan, published, userid');
}

function getRecentNotices($blogid) {
	$context = Model_Context::getInstance();
	$query = getDefaultDBModelOnNotice($blogid);
	$query->setLimit($context->getProperty('skin.noticesOnRecent'));
	return $query->getAll('id, title, slogan, published, userid');
}
function getDefaultDBModelOnNotice($blogid) {
	$context = Model_Context::getInstance();
	$query = DBModel::getInstance();
	$query->reset('Entries');
	$query->setQualifier('blogid','equals',$blogid);
	$query->setQualifier('draft','equals',0);
	if(!doesHaveOwnership()) {
		$query->setQualifier('visibility','bigger',1);
	}
	$query->setQualifier('category','equals',-2);
	$query->setOrder('published','DESC');
	return $query;
}
?>
