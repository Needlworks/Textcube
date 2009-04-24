<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

global $serviceSetting;
global $userSetting;
$serviceSetting = array();
$userSetting = array();

function clearServiceSettingCache() {
	global $serviceSetting;
	if( !empty($serviceSetting) ) {
		$serviceSetting = array();
	}
}

function clearUserSettingCache() {
	global $userSetting;
	if( !empty($userSetting) ) {
		$userSetting = array();
	}
}

function getServiceSetting($name, $default = null) {
	global $database;
	global $serviceSetting;
	global $gCacheStorage;
	if( empty($serviceSetting) ) {
		$settings = POD::queryAllWithCache("SELECT name, value FROM {$database['prefix']}ServiceSettings" , MYSQL_NUM );
		foreach( $settings as $k => $v ) {
			$serviceSetting[ $v[0] ] = $v[1];
		}
	}
	if( isset($serviceSetting[$name]) ) {
		return $serviceSetting[$name];
	}
	return $default;
}

function setServiceSetting($name, $value) {
	global $database;
	$name = POD::escapeString(UTF8::lessenAsEncoding($name, 32));
	$value = POD::escapeString($value);
	clearServiceSettingCache();
	return POD::execute("REPLACE INTO {$database['prefix']}ServiceSettings VALUES('$name', '$value')");
}

function removeServiceSetting($name,$pruneSimilarEntries = false) {
	global $database;
	clearServiceSettingCache();
	if($pruneSimilarEntries) return POD::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name like '".POD::escapeString($name)."'");
	else return POD::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name = '".POD::escapeString($name)."'");
}

function getBlogSetting($name, $default = null, $blogid = null) {
	return Model_Setting::getBlogSettingGlobal($name, $default);
}

function setBlogSetting($name, $value, $blogid = null) {
	return Model_Setting::setBlogSettingGlobal($name, $value, $blogid);
}

function setBlogSettingDefault($name, $value, $blogid = null) {
	return Model_Setting::setBlogSettingDefault($name, $value, $blogid);
}

function removeBlogSetting($name, $blogid = null) {
	return Model_Setting::removeBlogSettingGlobal($name, $blogid);
}

function getUserSetting($name, $default = null, $userid = null) {
	return Model_Setting::getUserSettingGlobal($name, $default, $userid);
}

function setUserSetting($name, $value, $userid = null) {
	return Model_Setting::setUserSettingGlobal($name, $value, $userid);
}

function removeUserSetting($name, $userid = null) {
	return Model_Setting::removeUserSettingGlobal($name, $userid);
}

function getDefinedTableNames() {
	global $database;
	$prefix = $database['prefix'];
	$definedTables = array("
			{$prefix}Attachments", 
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
			"{$prefix}Utils_XMLRPCPingSettings");
	return $definedTables;
}
?>
