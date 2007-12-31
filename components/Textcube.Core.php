<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
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

	function getUserId($name) {
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
	function getHomepage($userid = null) {
		global $database;
		if (!isset($userid)) //TODO : 현재 로그인 사용자의 homepage만 변경가능.getUserSetting함수 특성. 
			$userid = getUserId();
		$homepage = getUserSetting("homepage");
		if(empty($homepage)) {
			$homepage = POD::queryCell("SELECT blogid FROM {$database['prefix']}TeamBlog WHERE userid = $userid ORDER BY acl DESC");
		}
		if(is_numeric($homepage)) {
			$homepage = getDefaultURL($homepage);
		}
		return $homepage;
	}

	/*@static@*/
	function setHomepage($homepage, $userid = null) {
		global $database;
		if (!isset($userid)) //TODO : 현재 로그인 사용자의 homepage만 변경가능.getUserSetting함수 특성. 
			$userid = getUserId();
		$result = setUserSetting("homepage",$homepage);
		return $result;
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
		$pid = md5( microtime(true) );
		while( isset( $_SESSION['pickle'][$pid] ) ) {
			$pid = md5( microtime(true) );
			usleep(50);
		}
		$_SESSION['pickle'][$pid] = $data;
		return $pid;
	}

	function unpickle( $pid ) {
		if( !isset( $_SESSION['pickle'] ) || !isset( $_SESSION['pickle'][$pid] ) ) {
			return null;
		}
		$data = $_SESSION['pickle'][$pid];
		unset( $_SESSION['pickle'][$pid] );
		return $data;
	}

	function repickle( $pid, & $data ) {
		if( empty($pid) ) {
			return;
		}
		$_SESSION['pickle'][$pid] = $data;
	}

	function taste( $pid ) {
		if( !isset( $_SESSION['pickle'] ) || !isset( $_SESSION['pickle'][$pid] ) ) {
			return null;
		}
		return $_SESSION['pickle'][$pid];
	}

	function debug( $pid = null ) {
		header( "X-Debug-tid: $pid" );
		foreach( $_SESSION['pickle'][$pid] as $k => $v ) {
			header( "X-Debug-$k: [$v]" );
		}
	}
}

?>
