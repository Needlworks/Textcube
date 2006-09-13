<?php

function getOwner($name) {
	global $database;
	return fetchQueryCell("select owner from {$database['prefix']}BlogSettings where name = '$name'");
}

function getOwnerBySecondaryDomain($domain) {
	global $database;
	return DBQuery::queryCell("SELECT owner FROM {$database['prefix']}BlogSettings WHERE secondaryDomain = '$domain' OR  secondaryDomain = '" . (substr($domain, 0, 4) == 'www.' ? substr($domain, 4) : 'www.' . $domain) . "'");
}

function getBlogSetting($owner) {
	global $database;
	if ($result = mysql_query("select * from {$database['prefix']}BlogSettings where owner = $owner")) {
		return mysql_fetch_array($result);
	}
	return false;
}

function getSkinSetting($owner) {
	global $database, $service;
	
	if ($result = mysql_query("SELECT * FROM {$database['prefix']}SkinSettings WHERE owner = $owner")) {
		$retval = mysql_fetch_array($result);
		if ($retval != FALSE) {
			if (!Validator::directory($retval['skin']) && ($retval['skin'] !="customize/$owner")) {
				$retval['skin'] = $service['skin'];
			}
			return $retval;
		}
	}
	
	$retval = array( 'owner' => $owner , 'skin' => $service['skin'], 
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
	
	return $retval;	
}

function getDefaultURL($uid) {
	global $database, $service;
	$blog = DBQuery::queryRow("SELECT name, secondaryDomain, defaultDomain FROM {$database['prefix']}BlogSettings WHERE owner = $uid");
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
?>
