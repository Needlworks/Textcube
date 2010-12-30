<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getPagesWithPaging($blogid, $search, $page, $count) {
	global $database, $folderURL, $suri;
	$aux = '';
	if (($search !== true) && $search) {
		$search = escapeSearchString($search);
		$aux = "AND (title LIKE '%$search%' OR content LIKE '%$search%')";
	}
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 1';
	$sql = "SELECT * FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 $visibility AND category = -3 $aux ORDER BY published DESC";
	return Paging::fetch($sql, $page, $count, "$folderURL/{$suri['value']}");
}

function getPage($blogid, $id) {
	$query = getDefaultDBModelOnPage($blogid);
	$query->setQualifier('id','equals',$id);
	return $query->getAll('id, title, slogan, published, userid');
}

function getPages($blogid) {
	$query = getDefaultDBModelOnPage($blogid);
	return $query->getAll('id, title, slogan, published, userid');
}

function getRecentPages($blogid) {
	$context = Model_Context::getInstance();
	$query = getDefaultDBModelOnPage($blogid);
	$query->setLimit($context->getProperty('skin.pagesOnRecent',10));
	return $query->getAll('id, title, slogan, published, userid');
}
function getDefaultDBModelOnPage($blogid) {
	$context = Model_Context::getInstance();
	$query = DBModel::getInstance();
	$query->reset('Entries');
	$query->setQualifier('blogid','equals',$blogid);
	$query->setQualifier('draft','equals',0);
	if(!doesHaveOwnership()) {
		$query->setQualifier('visibility','bigger',1);
	}
	$query->setQualifier('category','equals',-3);
	$query->setOrder('published','DESC');
	return $query;
}
?>
