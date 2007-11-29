<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
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
	if( empty($serviceSetting) ) {
		$settings = DBQuery::queryAllWithCache("SELECT name, value FROM {$database['prefix']}ServiceSettings" , MYSQL_NUM );
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
	$name = DBQuery::escapeString(UTF8::lessenAsEncoding($name, 32));
	$value = DBQuery::escapeString($value);
	clearServiceSettingCache();
	return DBQuery::execute("REPLACE INTO {$database['prefix']}ServiceSettings VALUES('$name', '$value')");
}

function removeServiceSetting($name) {
	global $database;
	clearServiceSettingCache();
	return DBQuery::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name = '".DBQuery::escapeString($name)."'");
}

function getBlogSetting($name, $default = null, $blogid = null) {
	$settings = getBlogSettings(getBlogId()); // from blog.service.php
	if ($settings === false) return $default;
	if( isset($settings[$name]) ) {
		return $settings[$name];
	}
	return $default;
}

function setBlogSetting($name, $value, $blogid = null) {
	global $database;
	global $__gCacheBlogSettings; // share blog.service.php

	if (is_null($blogid)) $blogid = getBlogId();
	if (!is_numeric($blogid)) return null;

	if (!array_key_exists($blogid, $__gCacheBlogSettings)) {
		// force loading
		getBlogSettings($blogid);
	}
	if ($__gCacheBlogSettings[$blogid] === false) {
		return null;
	}
	
	$escape_name = DBQuery::escapeString($name);
	$escape_value = DBQuery::escapeString($value);
	
	if (array_key_exists($name, $__gCacheBlogSettings[$blogid])) {
		// overwrite value
		$__gCacheBlogSettings[$blogid][$name] = $value;
		return DBQuery::execute("REPLACE INTO {$database['prefix']}BlogSettings VALUES($blogid, '$escape_name', '$escape_value')");
	}
	
	// insert new value
	$__gCacheBlogSettings[$blogid][$name] = $value;
	return DBQuery::execute("INSERT INTO {$database['prefix']}BlogSettings VALUES($blogid, '$escape_name', '$escape_value')");
}

function setBlogSettingDefault($name, $value, $blogid = null) {
	global $database;
	$name = DBQuery::escapeString($name);
	$value = DBQuery::escapeString($value);
	if($blogid == null)
		return DBQuery::execute("REPLACE INTO {$database['prefix']}BlogSettings VALUES(".getBlogId().", '$name', '$value')");
	else if(is_numeric($blogid)) {
		return DBQuery::execute("REPLACE INTO {$database['prefix']}BlogSettings VALUES($blogid, '$name', '$value')");
	}
	return null;
}

function removeBlogSetting($name, $blogid = null) {
	global $database;
	global $__gCacheBlogSettings; // share blog.service.php

	if (is_null($blogid)) $blogid = getBlogId();
	if (!is_numeric($blogid)) return null;

	if (!array_key_exists($blogid, $__gCacheBlogSettings)) {
		// force loading
		getBlogSettings($blogid);
	}
	if ($__gCacheBlogSettings[$blogid] === false) {
		return null;
	}
	
	$escape_name = DBQuery::escapeString($name);
	
	if (array_key_exists($name, $__gCacheBlogSettings[$blogid])) {
		// overwrite value
		unset($__gCacheBlogSettings[$blogid][$name]);
		return DBQuery::execute("DELETE FROM {$database['prefix']}BlogSettings 
			WHERE blogid = $blogid AND name = '$escape_name'");
	}
	
	// already not exist
	return true;
}

function getUserSetting($name, $default = null) {
	global $database;
	global $userSetting;
	if( empty($userSetting) ) {
		$settings = DBQuery::queryAllWithCache("SELECT name, value 
				FROM {$database['prefix']}UserSettings
				WHERE userid = ".getUserId(), MYSQL_NUM );
		foreach( $settings as $k => $v ) {
			$userSetting[ $v[0] ] = $v[1];
		}
	}
	if( isset($userSetting[$name]) ) {
		return $userSetting[$name];
	}
	return $default;

	global $database;
	$value = DBQuery::queryCell("SELECT value 
			FROM {$database['prefix']}UserSettings 
			WHERE userid = ".getUserId()."
				AND name = '".DBQuery::escapeString($name)."'");
	return ($value === null) ? $default : $value;
}

function setUserSetting($name, $value) {
	global $database;
	$name = DBQuery::escapeString($name);
	$value = DBQuery::escapeString($value);
	clearUserSettingCache();
	return DBQuery::execute("REPLACE INTO {$database['prefix']}UserSettings VALUES(".getUserId().", '$name', '$value')");
}

function removeUserSetting($name) {
	global $database;
	clearUserSettingCache();
	return DBQuery::execute("DELETE FROM {$database['prefix']}UserSettings 
			WHERE userid = ".getUserId()." AND name = '".DBQuery::escapeString($name)."'");
}

function getDefinedTableNames() {
	global $database;
	$prefix = $database['prefix'];
	$definedTables = array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Links", "{$prefix}PageCacheLog", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}Teamblog", "{$prefix}TeamEntryRelations", "{$prefix}TrackbackLogs", "{$prefix}Trackbacks", "{$prefix}Users", "{$prefix}UserSettings", "{$prefix}XMLRPCPingSettings");
	return $definedTables;
}
?>
