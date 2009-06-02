<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

global $userSetting;
$userSetting = array();

function clearServiceSettingCache() {
	global $__serviceSetting;
	if( !empty($__serviceSetting) ) {
		$__serviceSetting = array();
	}
}

function clearUserSettingCache() {
	global $userSetting;
	if( !empty($userSetting) ) {
		$userSetting = array();
	}
}

function getServiceSetting($name, $default = null) {
	requireComponent('Textcube.Function.Setting');
	return setting::getServiceSetting($name, $default);
}

function setServiceSetting($name, $value) {
	requireComponent('Textcube.Function.Setting');
	return setting::setServiceSettingGlobal($name, $value);
}

function removeServiceSetting($name,$pruneSimilarEntries = false) {
	global $database;
	clearServiceSettingCache();
	if($pruneSimilarEntries) return POD::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name like '".POD::escapeString($name)."'");
	else return POD::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name = '".POD::escapeString($name)."'");
}

function getBlogSetting($name, $default = null, $blogid = null) {
	requireComponent('Textcube.Function.Setting');
	return setting::getBlogSettingGlobal($name, $default);
}

function setBlogSetting($name, $value, $blogid = null) {
	requireComponent('Textcube.Function.Setting');
	return setting::setBlogSettingGlobal($name, $value, $blogid);
}

function setBlogSettingDefault($name, $value, $blogid = null) {
	requireComponent('Textcube.Function.Setting');
	return setting::setBlogSettingDefault($name, $value, $blogid);
}

function removeBlogSetting($name, $blogid = null) {
	requireComponent('Textcube.Function.Setting');
	return setting::removeBlogSettingGlobal($name, $blogid);
}

function getUserSetting($name, $default = null, $userid = null) {
	requireComponent('Textcube.Function.Setting');
	return setting::getUserSettingGlobal($name, $default, $userid);
}

function setUserSetting($name, $value, $userid = null) {
	requireComponent('Textcube.Function.Setting');
	return setting::setUserSettingGlobal($name, $value, $userid);
}

function removeUserSetting($name, $userid = null) {
	requireComponent('Textcube.Function.Setting');
	return setting::removeUserSettingGlobal($name, $userid);
}

function getDefinedTableNames() {
	global $database;
	$prefix = $database['prefix'];
	$definedTables = array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}EntriesArchive", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}OpenIDUsers", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Links", "{$prefix}LinkCategories", "{$prefix}PageCacheLog", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}Teamblog", "{$prefix}TeamEntryRelations", "{$prefix}TrackbackLogs", "{$prefix}Trackbacks", "{$prefix}Users", "{$prefix}UserSettings", "{$prefix}XMLRPCPingSettings");
	return $definedTables;
}
?>
