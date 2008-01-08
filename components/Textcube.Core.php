<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

requireComponent( "Textcube.Control.Auth" );

// for Global Cache
global $__gCacheUserNames;
$__gCacheUserNames = array();

class User {
	/*@static@*/
	function getName($userid = null) {
		global $database, $__gCacheUserNames;
		if (!isset($userid))
			$userid = getUserId();
		if (array_key_exists($userid, $__gCacheUserNames)) {
			return $__gCacheUserNames[$userid];
		}
		return $__gCacheUserNames[$userid] = POD::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = $userid");
	}

	function getUserIdByName($name) {
		global $database, $__gCacheUserNames;
		if (!isset($name))
			return getUserId();
		$name = POD::escapeString($name);
		$userid = array_search($name, $__gCacheUserNames);
		if(!empty($userid))
			return $userid;
		$userid = POD::queryCell("SELECT userid FROM {$database['prefix']}Users WHERE name = '".$name."'");
		$__gCacheUserNames[$userid] = $name;
		return $userid;
	}

	/*@static@*/
	function getBlogOwnerName($blogid) {
		global $database;
		$ownerUserId = POD::queryCell("SELECT userid 
			FROM {$database['prefix']}Teamblog
			WHERE blogid = $blogid
				AND acl > 15");
		return User::getName($ownerUserId);
	}
	/*@static@*/
	function getEmail($userid = null) {
		global $database;
		if (!isset($userid))
			$userid = getUserId();
		return POD::queryCell("SELECT loginid FROM {$database['prefix']}Users WHERE userid = $userid");
	}

	/*@static@*/
	function getBlogs($userid = null) {
		global $database;
		if (!isset($userid))
			$userid = getUserId();
		return POD::queryColumn("SELECT blogid FROM {$database['prefix']}TeamBlog WHERE userid = $userid");
	}

	/*@static@*/
	function getOwnedBlogs($userid = null) {
		global $database;
		if (!isset($userid))
			$userid = getUserId();
		return POD::queryColumn("SELECT blogid FROM {$database['prefix']}TeamBlog WHERE userid = $userid AND acl > 15");
	}

	/*@static@*/
	function getHomepageType($userid = null) {
		global $database;
		if (!isset($userid))//TODO : 현재 로그인 사용자의 homepage만 읽기가능.getUserSetting함수 특성. 
			$userid = getUserId();
		$type = getUserSetting("homepagetype");
		if (empty($type)) {
			$type = "default";
		}
		return $type;
	}

	/*@static@*/
	function getHomepage($userid = null) {
		global $database;
		if (!isset($userid)) //TODO : 현재 로그인 사용자의 homepage만 읽기가능.getUserSetting함수 특성. 
			$userid = getUserId();
		$type = User::getHomepageType();
		switch ($type) {
		case "external" :
			$homepage = getUserSetting("homepage");
			break;
		case "internal" :
			$homepage = getDefaultURL(getUserSetting("blogidforhomepage"));
			break;
		case "author" : 
			$homepage = getDefaultURL(getUserSetting("blogidforhomepage"))."/author/".User::getName();
			break;
		case "default" :
		default :
			$homepage = null;
		}
		return $homepage;
	}

	function setHomepage($type, $homepage, $blogid = null, $userid = null) {
		global $database;
		if (!isset($userid)) //TODO : 현재 로그인 사용자의 homepage만 변경가능.setUserSetting함수 특성. 
			$userid = getUserId();
		if (setUserSetting("homepagetype",$type)) {
			switch ($type) {
			case "internal" :
			case "author" : 
				$homepage = null;
				break;
			case "external" :
				$blogid = null;
				break;
			case "default" :
			default :
				$homepage = null;
				$blogid = null;
			}
		}
		else {
			return false;
		}
		if (setUserSetting("homepage",$homepage)) {
			if (setUserSetting("blogidforhomepage",$blogid)) {
				return true;
			}
		}
		return false;
	}
	
	/*@static@*/
	function confirmPassword($password) {
		global $database;
		$password = md5($password);
		return POD::queryExistence("SELECT userid FROM {$database['prefix']}Users WHERE userid = ".getBlogId()." AND password = '$password'");
	}

	function authorName($blogid = null,$entryId){
		if( is_null($blogid) ) {
			$blogid = getBlogId();
		}
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
	
		$changeBlogView = str_repeat(TAB,7)."<select id=\"teamblog\" onchange=\"location.href='{$blogURL}/owner/setting/teamblog/changeBlog/?blogid='+this.value\">".CRLF;
		
		$teamblogListInfo = POD::queryAll("SELECT t.blogid, b.value AS title
				FROM {$database['prefix']}Teamblog t 
				LEFT JOIN {$database['prefix']}BlogSettings b ON b.blogid = t.blogid AND b.name = 'title'
				WHERE t.userid='".getUserId()."'");
		foreach($teamblogListInfo as $info){
			$title = empty($info['title']) ? _f('%1 님의 블로그',User::getBlogOwnerName($info['blogid'])) : UTF8::lessenAsEm($info['title'],30);
			$changeBlogView .= str_repeat(TAB,8).'<option value="' . $info['blogid'] . '"';
			if($info['blogid'] == $blogid) $changeBlogView .= ' selected="selected"';
			$changeBlogView .= '>' . $title . '</option>'.CRLF;
		}
		$changeBlogView .= str_repeat(TAB,7).'</select>'.CRLF;
		return $changeBlogView;
	}
}

class Transaction {
	function pickle($data) {
		if( !isset( $_SESSION['pickle'] ) ) {
			$_SESSION['pickle'] = array();
		}
		$tid = md5( microtime(true) );
		while( isset( $_SESSION['pickle'][$tid] ) ) {
			$tid = md5( microtime(true) );
			usleep(50);
		}
		$_SESSION['pickle'][$tid] = $data;
		return $tid;
	}

	function unpickle( $tid ) {
		if( !isset( $_SESSION['pickle'] ) || !isset( $_SESSION['pickle'][$tid] ) ) {
			return null;
		}
		$data = $_SESSION['pickle'][$tid];
		unset( $_SESSION['pickle'][$tid] );
		if( empty( $_SESSION['pickle'] ) ) {
			unset( $_SESSION['pickle'] );
		}
		return $data;
	}

	function repickle( $tid, & $data ) {
		if( empty($tid) ) {
			return;
		}
		$_SESSION['pickle'][$tid] = $data;
	}

	function taste( $tid ) {
		if( !isset( $_SESSION['pickle'] ) || !isset( $_SESSION['pickle'][$tid] ) ) {
			return null;
		}
		return $_SESSION['pickle'][$tid];
	}

	function clear() {
		if( isset( $_SESSION['pickle'] ) ) {
			unset( $_SESSION['pickle'] );
		}
	}

	function debug( $tid = null ) {
		header( "X-Debug-tid: $tid" );
		foreach( $_SESSION['pickle'][$tid] as $k => $v ) {
			header( "X-Debug-$k: [$v]" );
		}
	}
}

?>
