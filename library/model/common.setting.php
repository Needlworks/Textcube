<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

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
	return Setting::getServiceSetting($name, $default, true);
}

function setServiceSetting($name, $value) {
	return Setting::setServiceSettingGlobal($name, $value);
}

function removeServiceSetting($name,$pruneSimilarEntries = false) {
	global $database;
	clearServiceSettingCache();
	if($pruneSimilarEntries) return POD::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name like '".POD::escapeString($name)."'");
	else return POD::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name = '".POD::escapeString($name)."'");
}

function getBlogSetting($name, $default = null, $blogid = null) {
	return Setting::getBlogSettingGlobal($name, $default);
}

function setBlogSetting($name, $value, $blogid = null) {
	return Setting::setBlogSettingGlobal($name, $value, $blogid);
}

function setBlogSettingDefault($name, $value, $blogid = null) {
	return Setting::setBlogSettingDefault($name, $value, $blogid);
}

function removeBlogSetting($name, $blogid = null) {
	return Setting::removeBlogSettingGlobal($name, $blogid);
}

function getUserSetting($name, $default = null, $userid = null) {
	return Setting::getUserSettingGlobal($name, $default, $userid);
}

function setUserSetting($name, $value, $userid = null) {
	return Setting::setUserSettingGlobal($name, $value, $userid);
}

function removeUserSetting($name, $userid = null) {
	return Setting::removeUserSettingGlobal($name, $userid);
}

function getDefinedTableNames() {
	global $database;
	$prefix = $database['prefix'];
	$definedTables = array(
			"{$prefix}Attachments", 
			"{$prefix}BlogSettings", 
			"{$prefix}BlogStatistics", 
			"{$prefix}Categories", 
			"{$prefix}Comments", 
			"{$prefix}CommentsNotified", 
			"{$prefix}CommentsNotifiedQueue", 
			"{$prefix}CommentsNotifiedSiteInfo", 
			"{$prefix}DailyStatistics", 
			"{$prefix}Entries", 
			"{$prefix}EntriesArchive", 
			"{$prefix}FeedGroupRelations", 
			"{$prefix}FeedGroups", 
			"{$prefix}FeedItems", 
			"{$prefix}FeedReads", 
			"{$prefix}OpenIDUsers", 
			"{$prefix}Feeds", 
			"{$prefix}FeedSettings", 
			"{$prefix}FeedStarred", 
			"{$prefix}Filters", 
			"{$prefix}Lines", 
			"{$prefix}Links", 
			"{$prefix}LinkCategories", 
			"{$prefix}PageCacheLog", 
			"{$prefix}Plugins", 
			"{$prefix}RefererLogs", 
			"{$prefix}RefererStatistics", 
			"{$prefix}ReservedWords", 
			"{$prefix}ServiceSettings", 
			"{$prefix}Sessions", 
			"{$prefix}SessionVisits", 
			"{$prefix}SkinSettings", 
			"{$prefix}TagRelations", 
			"{$prefix}Tags", 
			"{$prefix}Privileges", 
			"{$prefix}TeamEntryRelations", 
			"{$prefix}RemoteResponseLogs", 
			"{$prefix}RemoteResponses", 
			"{$prefix}Users", 
			"{$prefix}UserSettings", 
			"{$prefix}Widgets", 
			"{$prefix}XMLRPCPingSettings");
	return $definedTables;
}
?>
