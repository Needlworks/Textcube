<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

requireComponent( "Textcube.Control.Auth" );

// for Global Cache
global $__gCacheUserNames;
$__gCacheUserNames = array();

function encodeURL($url) {
	global $service;
	if (isset($service['useEncodedURL']) && $service['useEncodedURL'])
		return str_replace('%2F', '/', rawurlencode($url));
	else
		return str_replace(array('%', ' ', '"', '#', '&', '\'', '<', '>', '?'), array('%25', '%20', '%22', '%23', '%26', '%27', '%3C', '%3E', '%3F'), $url);
}

class User {
	/*@static@*/
	function getName($userid = null) {
		global $database, $__gCacheUserNames;
		if (!isset($userid))
			$userid = getUserId();
		if (array_key_exists($userid, $__gCacheUserNames)) {
			return $__gCacheUserNames[$userid];
		}
		return $__gCacheUserNames[$userid] = DBQuery::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = $userid");
	}
	
	/*@static@*/
	function getEmail($userid = null) {
		global $database;
		if (!isset($userid))
			$userid = getUserId();
		return DBQuery::queryCell("SELECT loginid FROM {$database['prefix']}Users WHERE userid = $userid");
	}
	
	/*@static@*/
	function confirmPassword($password) {
		global $database;
		$password = md5($password);
		return DBQuery::queryExistence("SELECT userid FROM {$database['prefix']}Users WHERE userid = ".getBlogId()." AND password = '$password'");
	}

	function authorName($blogid = getBlogId(),$entryId){
		requireComponent('Eolin.PHP.Core');
		global $database, $entry;

		// Read userId of entry from relation table.
		$userid = getUserIdOfEntry($blogid,$entryId);
		if(isset($userid)) {
			return User::getName($userid);
		} else {
			return false;
		}
	}

	function changeBlog(){
		global $database, $blogURL, $blog, $service;
		$blogid = getBlogId();	
		if($service['type'] == "path")
			$path = str_replace($service['path']."/".$blog['name'], "", $_SERVER["REQUEST_URI"]);
		else
			$path = str_replace("/".$blog['name'], "", $_SERVER["REQUEST_URI"]);
	
		$blogn = "<select id=\"teamblog\" onchange=\"location.href='{$blogURL}/owner/setting/teamblog/changeBlog/?blogid='+this.value+'&path={$path}'\">";
	
		if( Acl::check('group.owners') ) {
			$myblogsel = '';
			if($blogid == getUserId()) $myblogsel = ' selected="selected"';
			$blogn .= '<option value="'.$blogid.'" '. $myblogsel .'>'._t('내 블로그').'</option>';
		}
	
		$teamblogInfo = DBQuery::queryAll("SELECT t.blogid, b.value AS title, u.name
				FROM {$database['prefix']}Teamblog t 
				LEFT JOIN {$database['prefix']}BlogSettings b ON b.blogid = t.blogid AND b.name = 'title'
				LEFT JOIN {$database['prefix']}Users u ON u.userid = t.blogid
				WHERE t.userid='".getUserId()."'");
		foreach($teamblogInfo as $teamInfo){
			if($teamInfo['blogid'] == $blogid && $blogid == getUserId()){
				continue;
			} else {
				$title = empty($teamInfo['title']) ? _f('%1 님의 블로그',$teamInfo['name']) : $teamInfo['title'];
				$blogn .= '<option value="' . $teamInfo['blogid'] . '"';
				if($teamInfo['blogid'] == $blogid) $blogn .= ' selected="selected"';
				$blogn .= '>' . $title . '</option>';
			}
		}
		$blogn .= '</select>';
		return $blogn;
	}
}

?>
