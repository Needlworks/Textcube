<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// for Global Cache
$__gCacheBlogSettings = array();

function getBlogidByName($name) {
	global $database;
	$query = new TableQuery($database['prefix'] . 'BlogSettings');
	$query->setQualifier('name','name');
	$query->setQualifier('value', $name, true);
	return $query->getCell('blogid');
	return false;
}

function getBlogidBySecondaryDomain($domain) {
	global $database;
	return POD::queryCell("SELECT blogid FROM {$database['prefix']}BlogSettings WHERE name = 'secondaryDomain' AND (value = '$domain' OR  value = '" . (substr($domain, 0, 4) == 'www.' ? substr($domain, 4) : 'www.' . $domain) . "')");
}

function getBlogSettings($blogid) {
	requireComponent('Textcube.Function.Setting');
	return setting::getBlogSettingsGlobal($blogid);
}

function getSkinSetting($blogid, $forceReload = false) {
	global $database, $service, $skinSetting;
	global $__gCacheSkinSetting;
	global $gCacheStorage;
	if (
		($forceReload == false) 
		&& (isset($__gCacheSkinSetting)) 
		&& (array_key_exists($blogid, $__gCacheSkinSetting))
		) 
	{
		return $__gCacheSkinSetting[$blogid];
	}
	if($blogid == getBlogId() && $forceReload == false) {
		$retval = $gCacheStorage->getContent('SkinSetting');
		if(!empty($retval)) {
			$__gCacheSkinSetting[$blogid] = $retval;
			return $retval;
		}
	}
	if ($retval = POD::queryRow("SELECT * FROM {$database['prefix']}SkinSettings WHERE blogid = $blogid",MYSQL_ASSOC)) {
		if ($retval != FALSE) {
			if (!Validator::directory($retval['skin']) && ($retval['skin'] !="customize/$blogid")) {
				$retval['skin'] = $service['skin'];
			}
			$__gCacheSkinSetting[$blogid] = $retval;
			if($blogid == getBlogId())  $gCacheStorage->setContent('SkinSetting',$retval);
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
	if($blogid == getBlogId())  $gCacheStorage->setContent('SkinSetting',$retval);
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
			return "http://$domain$path".getFancyURLpostfix();
	}
}

function getFancyURLpostfix() {
	global $service;
	switch($service['fancyURL']) {
		case 0: return '/index.php?';
		case 1: return '/?';
		case 2:default: return '';
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
	global $blog;
	return 'http://help.tattertools.com/'.$blog['language'].'/Textcube/Menu/'.$path;
}
?>
