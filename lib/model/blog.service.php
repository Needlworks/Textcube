<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getOwner($name) {
	global $database;
	return DBQuery::queryCell("select owner from {$database['prefix']}BlogSettings where name = '$name'");
}

function getOwnerBySecondaryDomain($domain) {
	global $database;
	return DBQuery::queryCell("SELECT owner FROM {$database['prefix']}BlogSettings WHERE secondaryDomain = '$domain' OR  secondaryDomain = '" . (substr($domain, 0, 4) == 'www.' ? substr($domain, 4) : 'www.' . $domain) . "'");
}

function getBlogSettings($blogid) {
	global $database;
	$query = new TableQuery($database['prefix'] . 'BlogSettings');
	if($query->doesExist()){
		$query->setQualifier('owner',$blogid);
		$blogSettings = $query->getRow('*');
		if( $blogSettings ) {
			$result = array();
			foreach($blogSettings as $key => $value){
				if (is_int($key))
					continue;
				$result[$key] = $value;
			}
			return $result;
		}
	}
	return false;
}

function getSkinSetting($blogid) {
	global $database, $service, $skinSetting;
	
	if ($result = DBQuery::query("SELECT * FROM {$database['prefix']}SkinSettings WHERE owner = $blogid")) {
		$retval = mysql_fetch_array($result);
		if ($retval != FALSE) {
			if (!Validator::directory($retval['skin']) && ($retval['skin'] !="customize/$blogid")) {
				$retval['skin'] = $service['skin'];
			}
			return $retval;
		}
	}
	
	$retval = array( 'owner' => $blogid , 'skin' => $service['skin'], 
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

function getHelpURL($path) {
	return 'http://help.textcube.com/ko/Menu/'.$path;
}
?>
