<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getTrashTrackbackWithPagingForOwner($blogid, $category, $site, $url, $ip, $search, $page, $count) {
	return getRemoteResponsesWithPagingForOwner($blogid, $category, $site, $url, $ip, $search, $page, $count,'trackback',0);
}

function getTrashCommentsWithPagingForOwner($blogid, $category, $name, $ip, $search, $page, $count) {
	return getCommentsWithPagingForOwner($blogid, $category, $name, $ip, $search, $page, $count, true, 0);
}

function getTrackbackTrash($entry) {
	global $database;
	$trackbacks = array();
	$result = POD::queryAll("SELECT * 
			FROM {$database['prefix']}RemoteResponses 
			WHERE blogid = ".getBlogId()."
				AND entry = $entry 
			ORDER BY written",'assoc');
	if(!empty($result)) return $result;
	else return array();
}

function getRecentTrackbackTrash($blogid) {
	global $database;
	global $skinSetting;
	$trackbacks = array();
	$sql = doesHaveOwnership() ? "SELECT * FROM {$database['prefix']}RemoteResponses
		WHERE blogid = $blogid 
		ORDER BY written 
		DESC LIMIT {$skinSetting['trackbacksOnRecent']}" : 
		"SELECT t.* FROM {$database['prefix']}RemoteResponses t, 
		{$database['prefix']}Entries e 
		WHERE t.blogid = $blogid AND t.blogid = e.blogid AND t.entry = e.id AND t.responsetype = 'trackback' AND e.draft = 0 AND e.visibility >= 2 
		ORDER BY t.written DESC LIMIT {$skinSetting['trackbacksOnRecent']}";
	if ($result = POD::queryAll($sql) && !empty($result)) {
		$trackbacks = $result;
//		while ($trackback = POD::fetch($result))
//			array_push($trackbacks, $trackback);
	}
	return $trackbacks;
}

function deleteTrackbackTrash($blogid, $id) {
	global $database;
	$entry = POD::queryCell("SELECT entry FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!POD::execute("DELETE FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function restoreTrackbackTrash($blogid, $id) {
   	$pool = DBModel::getInstance();
   	$pool->reset('RemoteResponses');
   	$pool->setQualifier('blogid','eq',$blogid);
   	$pool->setQualifier('id','eq',$id);
   	$entry = $pool->getCell('entry');
 	if ($entry === null)
		return false;
	$pool->setAttribute('isfiltered',0);
	if(!$pool->update())
		return false;
	if (updateTrackbacksOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function trashVan() {
	$context = Model_Context::getInstance();
	if(Timestamp::getUNIXtime() - Setting::getServiceSetting('lastTrashSweep',0, true) > 43200) {
		$pool = DBModel::getInstance();
		$pool->reset('Comments');
		$pool->setQualifier('isfiltered','s',Timestamp::getUNIXtime()-$context->getProperty('service.trashtimelimit',302400));
		$pool->setQualifier('isfiltered','b',0);
		$pool->delete();
		$pool->reset('RemoteResponses');
		$pool->setQualifier('isfiltered','s',Timestamp::getUNIXtime()-$context->getProperty('service.trashtimelimit',302400));
		$pool->setQualifier('isfiltered','b',0);
		$pool->delete();
		$pool->reset('RefererLogs');
		$pool->setQualifier('referred','s',Timestamp::getUNIXtime()-604800);
		$pool->delete();
		Setting::setServiceSetting('lastTrashSweep',Timestamp::getUNIXtime(),true);
	}
	if(Timestamp::getUNIXtime() - Setting::getServiceSetting('lastNoticeRead',0, true) > 43200) {
		Setting::removeServiceSetting('TextcubeNotice',true);
		Setting::setServiceSetting('lastNoticeRead',Timestamp::getUNIXtime(),true);
	}
}

function emptyTrash($comment = true, $blogid = null) {
	$pool = DBModel::getInstance();
	if (is_null($blogid)) {
		$blogid = getBlogId();
	}
	if ($comment == true) {
		$pool->reset('Comments');
		$pool->setQualifier('blogid','eq',$blogid);
		$pool->setQualifier('isfiltered','b',0);
		$pool->delete();
	} else {
		$pool->reset('RemoteResponses');
		$pool->setQualifier('blogid','eq',$blogid);
		$pool->setQualifier('isfiltered','b',0);
		$pool->delete();
	}
}
?>
