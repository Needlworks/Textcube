<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// for Global Cache
$__gCacheBlogSettings = array();

function getBlogidByName($name) {
	global $database;
	$query = new TableQuery($database['prefix'] . 'BlogSettings');
	if($query->doesExist()) {
		$query->setQualifier('name','name');
		$query->setQualifier('value', $name, true);
		return $query->getCell('blogid');
	}
	return false;
}

function getBlogidBySecondaryDomain($domain) {
	global $database;
	return DBQuery::queryCell("SELECT blogid FROM {$database['prefix']}BlogSettings WHERE secondaryDomain = '$domain' OR  secondaryDomain = '" . (substr($domain, 0, 4) == 'www.' ? substr($domain, 4) : 'www.' . $domain) . "'");
}

function getBlogSettings($blogid) {
	global $database, $service;
	global $__gCacheBlogSettings;
	if (array_key_exists($blogid, $__gCacheBlogSettings)) {
		return $__gCacheBlogSettings[$blogid];
	}
	$query = new TableQuery($database['prefix'] . 'BlogSettings');
	$query->setQualifier('blogid',$blogid);
	$blogSettings = $query->getAll();
	if( $blogSettings ) {
		$result = array();
		$blogSettingFields = array();
		$defaultValues = array(
				'name'                     => '',
				'defaultDomain'            => 0,
				'title'                    => '', 
				'description'              => '', 
				'logo'                     => '', 
				'logoLabel'                => '', 
				'logoWidth'                => 0,
				'logoHeight'               => 0,
				'useSlogan'                => 1,
				'entriesOnPage'            => 10, 
				'entriesOnList'            => 10, 
				'entriesOnRSS'             => 10, 
				'publishWholeOnRSS'        => 1,
				'publishEolinSyncOnRSS'    => 0,
				'allowWriteOnGuestbook'    => 1,
				'allowWriteDblCommentOnGuestbook' => 1,
				'language'     => $service['language'],
				'blogLanguage' => $service['language'],
				'timezone'     => $service['timezone'],
				'noneCommentMessage'       => '',
				'singleCommentMessage'     => '',
				'noneTrackbackMessage'     => '',
				'singleTrackbackMessage'   => '');
		foreach($blogSettings as $blogSetting) {
			$result[$blogSetting['name']] = $blogSetting['value'];
			if(array_key_exists($blogSetting['name'],$defaultValues)) {
				array_push($blogSettingFields, $blogSetting['name']);
			}
		}
		foreach($defaultValues as $name => $value) {
			if(!in_array($name,$blogSettingFields)) {
				$result[$name] = $value;
				setBlogSettingDefault($name,$value);
			}
		}
		$__gCacheBlogSettings[$blogid] = $result;
		return $result;
	}
	$__gCacheBlogSettings[$blogid] = false;
	return false;
}

function getSkinSetting($blogid, $forceReload = false) {
	global $database, $service, $skinSetting;
	global $__gCacheSkinSetting;
	
	if (
		($forceReload == false) 
		&& (isset($__gCacheSkinSetting)) 
		&& (array_key_exists($blogid, $__gCacheSkinSetting))
		) 
	{
		return $__gCacheSkinSetting[$blogid];
	}
	
	if ($result = DBQuery::query("SELECT * FROM {$database['prefix']}SkinSettings WHERE blogid = $blogid")) {
		$retval = mysql_fetch_array($result);
		if ($retval != FALSE) {
			if (!Validator::directory($retval['skin']) && ($retval['skin'] !="customize/$blogid")) {
				$retval['skin'] = $service['skin'];
			}
			$__gCacheSkinSetting[$blogid] = $retval;
			return $retval;
		}
	}
	
	$retval = array( 'blogid' => $blogid , 'skin' => $service['skin'], 
		'entriesOnRecent' => 5, 'commentsOnRecent' => 5, 'commentsOnGuestbook' => 5,
		'tagsOnTagbox' => 30, 'tagboxAlign' => 3, 'trackbacksOnRecent' => 5, 
		'expandComment' => 1, 'expandTrackback' => 1, 
		'recentNoticeLength' => 25, 'recentEntryLength' => 30, 
		'recentCommentLength' => 30, 'recentTrackbackLength' => 30, 
		'linkLength' => 30, 'showListOnCategory' => 1, 'showListOnArchive' => 1, 
		'tree' => 'base', 
		'colorOnTree' => '000000', 'bgColorOnTree' => '', 
		'activeColorOnTree' => 'FFFFFF', 'activeBgColorOnTree' => '00ADEF', 
		'labelLengthOnTree' => 27, 'showValueOnTree' => 1 );
	
	$__gCacheSkinSetting[$blogid] = $retval;
	return $retval;	
}

function getDefaultURL($blogid) {
	global $database, $service;
	$blog = getBlogSettings( $blogid );
	switch ($service['type']) {
		case 'domain':
			if ($blog['defaultDomain'] && $blog['secondaryDomain'])
				return ('http://' . $blog['secondaryDomain'] . (isset($service['port']) ? ':' . $service['port'] : '') . $service['path']);
			else
				return ('http://' . $blog['name'] . '.' . $service['domain'] . (isset($service['port']) ? ':' . $service['port'] : '') . $service['path']);
		case 'path':
			return ('http://' . $service['domain'] . (isset($service['port']) ? ':' . $service['port'] : '') . $service['path'] . '/' . $blog['name']);
		case 'single':
		default:
			return ('http://' . $service['domain'] . (isset($service['port']) ? ':' . $service['port'] : '') . $service['path']);
	}
}

function getBlogURL($name = null, $domain = null, $path = null, $type = null) {
	global $service, $blog;
	if ($type === null)
		$type = $service['type'];
	if ($path === null)
		$path = $service['path'];
	if ($domain === null)
		$domain = $service['domain'] . (isset($service['port']) ? ":{$service['port']}" : '');
	if ($name === null)
		$name = $blog['name'];
	switch ($type) {
		case 'domain':
			return "http://$name.$domain$path";
		case 'path':
			return "http://$domain$path/$name";
		case 'single':default:
			return "http://$domain$path";
	}
}

function getBlogURLRule($domain = null, $path = null, $type = null) {
	global $service, $blog;
	if ($type === null)
		$type = $service['type'];
	if ($path === null)
		$path = $service['path'];
	if ($domain === null)
		$domain = $service['domain'] . (isset($service['port']) ? ":{$service['port']}" : '');
	switch ($type) {
		case 'domain':
			return array('http://', ".$domain$path");
		case 'path':
			return array("http://$domain$path/", '');
		case 'single':default:
			return array("http://$domain$path", '');
	}
}

function getHelpURL($path) {
	return 'http://help.tattertools.com/textcube/Menu/'.$path;
}
?>
