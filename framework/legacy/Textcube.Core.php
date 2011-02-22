<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/* This component contains 'User', 'Blog' and 'Transaction' class. 
   NOTE : Classes described below are actually not object. Usually they are static.*/

// for Global Cache
global $__gCacheUserNames;
$__gCacheUserNames = array();

class User {
	private static $__gCacheUserNames;
	static function getName($userid = null) {
		global $database, $__gCacheUserNames;
		if (empty($userid))
			$userid = getUserId();
		if (array_key_exists($userid, $__gCacheUserNames)) {
			return $__gCacheUserNames[$userid];
		}
		return $__gCacheUserNames[$userid] = POD::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = $userid");
	}
	
	static function getUserIdByName($name) {
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
	
	static function getUserNamesOfBlog($blogid) {
		// TODO : Caching with global cache component. (Usually it is not changing easily.)
		global $database;
		$authorIds = POD::queryColumn("SELECT userid
			FROM {$database['prefix']}Privileges
			WHERE blogid = $blogid");
		$authorInfo = POD::queryAll("SELECT userid, name
			FROM {$database['prefix']}Users
			WHERE userid IN (".implode(",",$authorIds).")");
		return $authorInfo;
	}

	static function getBlogOwnerName($blogid) {
		global $database;
		$ownerUserId = POD::queryCell("SELECT userid 
			FROM {$database['prefix']}Privileges
			WHERE blogid = $blogid
				AND acl > 15");
		return User::getName($ownerUserId);
	}

	static function getBlogOwner($blogid) {
		global $database;
		$ownerUserId = POD::queryCell("SELECT userid 
			FROM {$database['prefix']}Privileges
			WHERE blogid = $blogid
				AND acl > 15");
		return $ownerUserId;
	}

	static function getEmail($userid = null) {
		global $database;
		if (!isset($userid))
			$userid = getUserId();
		return POD::queryCell("SELECT loginid FROM {$database['prefix']}Users WHERE userid = $userid");
	}

	static function getUserIdByEmail($loginid = null) {
		global $database;
		$loginid = trim($loginid);
		if(!isset($loginid)) return null;
		$loginid = POD::escapeString($loginid);
		return POD::queryCell("SELECT userid FROM {$database['prefix']}Users WHERE loginid = '".$loginid."'");
	}
	
	static function getBlogs($userid = null) {
		global $database;
		if (!isset($userid))
			$userid = getUserId();
		return POD::queryColumn("SELECT blogid FROM {$database['prefix']}Privileges WHERE userid = $userid");
	}

	static function getOwnedBlogs($userid = null) {
		global $database;
		if (!isset($userid))
			$userid = getUserId();
		return POD::queryColumn("SELECT blogid FROM {$database['prefix']}Privileges WHERE userid = $userid AND acl > 15");
	}

	static function getHomepageType($userid = null) {
		global $database;
		if (!isset($userid)) 
			$userid = getUserId();
		$info = unserialize(Setting::getUserSettingGlobal('userLinkInfo','',$userid));
		if(!empty($info)) $type = $info['type'];
		if (empty($type)) {
			$type = "default";
		}
		return $type;
	}

	static function getHomepage($userid = null) {
		global $database;
		if (!isset($userid) || empty($userid)) 
			$userid = getUserId();
		$info = unserialize(Setting::getUserSettingGlobal('userLinkInfo','',$userid));
		if(is_null($info)) $info = array('type' => 'default'); 
		switch ($info['type']) {
			case "external" :
				$homepage = $info['url'];
				break;
			case "internal" :
				$homepage = getDefaultURL($info['blogid']);
				break;
			case "author" : 
				$homepage = getDefaultURL($info['blogid'])."/author/".URL::encode(User::getName($userid));
				break;
			case "default" :
			default :
				$homepage = null;
		}
		return $homepage;
	}

	static function setHomepage($type, $homepage, $blogid = null, $userid = null) {
		global $database;
		$types = array("internal","author","external","default");
		if (!isset($userid)) //TODO : 현재 로그인 사용자의 homepage만 변경가능.setUserSetting함수 특성. 
			$userid = getUserId();
		$info['blogid'] = is_null($blogid) ? getBlogId() : $blogid;
		$info['url'] = is_null($homepage) ? null : $homepage;
		if (in_array($type,$types)) {
			$info['type'] = $type;
			switch ($type) {
				case "internal" : case "author" : 
					$info['url'] = null;
					break;
				case "external" :
					$info['blogid'] = null;
					break;
				case "default" :
				default :
					$info['url'] = null;
					$info['blogid'] = null;
			}
		} else {
			return false;
		}
		$homepage = serialize($info);
		if (Setting::setUserSettingGlobal("userLinkInfo",$homepage, $userid)) {
			return true;
		}
		return false;
	}
	
	static function confirmPassword($userid = null, $password) {
		global $database;
		if(empty($userid)) $userid = getUserId(); 
		$password = md5($password);
		return POD::queryExistence("SELECT userid FROM {$database['prefix']}Users WHERE userid = $userid AND password = '$password'");
	}

	static function authorName($blogid = null,$entryId){
		if( is_null($blogid) ) {
			$blogid = getBlogId();
		}

		// Read userId of entry from relation table.
		$userid = getUserIdOfEntry($blogid,$entryId);
		if(isset($userid)) {
			return User::getName($userid);
		} else {
			return false;
		}
	}
	static function changeBlog(){
		global $database, $blogURL, $blog, $service;
		$blogid = getBlogId();	

		$blogList = User::getBlogs();
		if (count($blogList) == 0) {
			return;
		}

		$changeBlogView = str_repeat(TAB,6)."<select id=\"blog-list\" onchange=\"location.href='{$blogURL}/owner/network/teamblog/changeBlog/?blogid='+this.value\">".CRLF;
		foreach($blogList as $info){
			$title = UTF8::lessen(Setting::getBlogSettingGlobal("title",null,$info,true), 30);
			$title = ($title ? $title : _f('%1 님의 블로그',User::getBlogOwnerName($info)));
			$changeBlogView .= str_repeat(TAB,7).'<option value="' . $info . '"';
			if($info == $blogid) $changeBlogView .= ' selected="selected"';
			$changeBlogView .= '>' . $title . '</option>'.CRLF;
		}
		$changeBlogView .= str_repeat(TAB,6).'</select>'.CRLF;
		return $changeBlogView;
	}
	
	static function changeSetting($userid, $email, $nickname) {
		global $database;
		if (strcmp($email, UTF8::lessenAsEncoding($email, 64)) != 0) return false;
		$email = POD::escapeString(UTF8::lessenAsEncoding($email, 64));
		$nickname = POD::escapeString(UTF8::lessenAsEncoding($nickname, 32));
		if ($email == '' || $nickname == '') {
			return false;
		}
		if (POD::queryExistence("SELECT * FROM {$database['prefix']}Users WHERE name = '$nickname' AND userid <> $userid")) {
			return false;
		} else {
			$result = POD::query("UPDATE {$database['prefix']}Users SET loginid = '$email', name = '$nickname' WHERE userid = $userid");		
			if (!$result) {
				return false;
			} else {
				return true;
			}
		}
	}

	static function add($email, $name) {
		global $database, $service, $user, $blog;
		if (empty($email))
			return 1;
		if (!preg_match('/^[^@]+@([-a-zA-Z0-9]+\.)+[-a-zA-Z0-9]+$/', $email))
			return 2;
	
		if (strcmp($email, UTF8::lessenAsEncoding($email, 64)) != 0) return 11;
	
		$loginid = POD::escapeString(UTF8::lessenAsEncoding($email, 64));	
		$name = POD::escapeString(UTF8::lessenAsEncoding($name, 32));
		$password = User::__generatePassword();
		$authtoken = md5(User::__generatePassword());
	
		if (POD::queryExistence("SELECT * FROM {$database['prefix']}Users WHERE loginid = '$loginid'")) {
			return 9;	// User already exists.
		}
	
		if (POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Users WHERE name = '$name'")) {
			$name = $name . '.' . time();
		}

		$result = POD::query("INSERT INTO {$database['prefix']}Users (userid, loginid, password, name, created, lastlogin, host) VALUES (".(User::__getMaxUserId()+1).", '$loginid', '" . md5($password) . "', '$name', UNIX_TIMESTAMP(), 0, ".getUserId().")");
		if (empty($result)) {
			return 11;
		}
		$result = POD::query("INSERT INTO {$database['prefix']}UserSettings (userid, name, value) VALUES ('".User::getUserIdByEmail($loginid)."', 'AuthToken', '$authtoken')");
		if (empty($result)) {
			return 11;
		}
		return true;
	}
	
	/*@static@*/
	function remove($userid) {
		global $database;
		if ($userid == 1)
			return false;
		if (!isset($userid)) 
			return false;
		$blogs = User::getOwnedBlogs($userid);
		$sql = "UPDATE {$database['prefix']}Comments SET replier = NULL WHERE replier = ".$userid;
		POD::execute($sql);
		foreach ($blogs as $ownedBlog) {
			Blog::changeOwner($ownedBlog,1); // 관리자 uid로 변경
		}
		$blogs = User::getBlogs($userid);
		foreach ($blogs as $joinedBlog) {
			Blog::deleteUser($joinedBlog, $userid);
		}
		User::removePermanent($userid);
		return true;
	}
	
	static function removePermanent($userid) {
		global $database;
		if( POD::execute("DELETE FROM {$database['prefix']}UserSettings WHERE userid = $userid AND name = 'AuthToken' LIMIT 1") ) {
			return POD::execute("DELETE FROM {$database['prefix']}Users WHERE userid = $userid");
		} else {
			return false;
		}
	}
	
	static function __generatePassword() {
		return strtolower(substr(base64_encode(rand(0x10000000, 0x70000000)), 3, 8));
	}

	/*@private static@*/
	function __getMaxUserId() {
		global $database;
		$maxId = POD::queryCell("SELECT max(userid) FROM {$database['prefix']}Users");
		if($maxId) return $maxId;
		else return 0;
	}
}

class Blog {
	/*@static@*/
	function changeOwner($blogid,$userid) {
		global $database;
		POD::execute("UPDATE {$database['prefix']}Privileges SET acl = 3 WHERE blogid = ".$blogid." and acl = " . BITWISE_OWNER);
	
		$acl = POD::queryCell("SELECT acl FROM {$database['prefix']}Privileges WHERE blogid = $blogid and userid = $userid");
	
		if( $acl === null ) { // If there is no ACL, add user into the blog.
			POD::query("INSERT INTO {$database['prefix']}Privileges  
				VALUES($blogid, $userid, '".BITWISE_OWNER."', UNIX_TIMESTAMP(), 0)");
		} else {
			POD::execute("UPDATE {$database['prefix']}Privileges SET acl = ".BITWISE_OWNER." 
				WHERE blogid = ".$blogid." and userid = " . $userid);
		}
		return true;
	}
	
	/*@static@*/
	/* TODO : remove model dependency (addBlog, sendInvitationMail) */
	function addUser($email, $name, $comment, $senderName, $senderEmail) {
		requireModel('blog.user');
		requireModel('blog.blogSetting');
		global $database,$service,$blogURL,$hostURL,$user,$blog;
	
		$blogid = getBlogId();
		if(empty($email))
			return 1;
		if(!preg_match('/^[^@]+@([-a-zA-Z0-9]+\.)+[-a-zA-Z0-9]+$/',$email))
			return array( 2, _t('이메일이 바르지 않습니다.') );
		
		$isUserExists = User::getUserIdByEmail($email);
		if(empty($isUserExists)) { // If user is not exist
			User::add($email,$name);
		}
		$userid = User::getUserIdByEmail($email);
		$result = addBlog(getBlogId(), $userid, null);
		if($result === true) {
			return sendInvitationMail(getBlogId(), $userid, User::getName($userid), $comment, $senderName, $senderEmail);
		}
		return $result;
	}

	/*@static@*/
	function deleteUser($blogid = null, $userid, $clean = true) {
		global $database;
		if ($blogid == null) {
			$blogid = getBlogId();
		}
		POD::execute("UPDATE {$database['prefix']}Entries 
			SET userid = ".User::getBlogOwner($blogid)." 
			WHERE blogid = ".$blogid." AND userid = ".$userid);
	
		// Delete ACL relation.
		if(!POD::execute("DELETE FROM {$database['prefix']}Privileges WHERE blogid='$blogid' and userid='$userid'"))
			return false;
		// And if there is no blog related to the specific user, delete user.
		if($clean && !POD::queryAll("SELECT * FROM {$database['prefix']}Privileges WHERE userid = '$userid'")) {
			User::removePermanent($userid);
		}
		return true;
	}
	
	/*@static@*/
	function changeACLofUser($blogid, $userid, $ACLtype, $switch) {  // Change user priviledge on the blog.
		global $database;
		if(empty($ACLtype) || empty($userid))
			return false;
		$acl = POD::queryCell("SELECT acl
				FROM {$database['prefix']}Privileges 
				WHERE blogid=$blogid and userid=$userid");
		if( $acl === null ) { // If there is no ACL, add user into the blog.
			$name = User::getName($userid);
			POD::query("INSERT INTO {$database['prefix']}Privileges  
					VALUES($blogid, $userid, 0, UNIX_TIMESTAMP(), 0)");
			$acl = 0;
		}
		$bitwise = null;
		switch( $ACLtype ) {
			case 'admin':
				$bitwise = BITWISE_ADMINISTRATOR;
				break;
			case 'editor':
				$bitwise = BITWISE_EDITOR;
				break;
			default:
				return false;
		}
		if( $switch ) {
			$acl |= $bitwise;
		} else {
			$acl &= ~$bitwise;
		}
		return POD::execute("UPDATE {$database['prefix']}Privileges 
			SET acl = ".$acl." 
			WHERE blogid = ".$blogid." and userid = ".$userid);
	}
}

class Transaction {
	function pickle($data) {
		$pickle_dir = ROOT.DS."cache".DS."pickle".DS;
		if( !isset( $_SESSION['pickle'] ) ) {
			$_SESSION['pickle'] = array();
		}
		$tid = sprintf("%010dP%s",time(),md5( microtime(true) ));
		while( file_exists( $pickle_dir.$tid ) ) {
			$tid = sprintf("%010dP%s",time(),md5( microtime(true) ));
			usleep(50);
		}
		file_put_contents( $pickle_dir.$tid, serialize($data) );
		return $tid;
	}

	function unpickle( $tid ) {
		$pickle_file = ROOT.DS."cache".DS."pickle".DS.$tid;
		$data = unserialize(file_get_contents( $pickle_file ));
		unlink( $pickle_file );
		return $data;
	}

	function repickle( $tid, & $data ) {
		if( empty($tid) ) {
			return;
		}
		$_SESSION['pickle'][$tid] = $data;
		$pickle_dir = ROOT.DS."cache".DS."pickle".DS;
		if( !file_exists( $pickle_dir ) ) {
			mkdir( $pickle_dir );
		}
		file_put_contents( $pickle_dir.$tid, serialize($data) );
	}

	function taste( $tid ) {
		$pickle_file = ROOT.DS."cache".DS."pickle".DS.$tid;
		if( !file_exists( $pickle_file ) ) {
			return null;
		}
		$data = unserialize(file_get_contents( $pickle_file ));
		return $data;
	}

	function clear() {
		return;
	}

	function gc() {
		return;
	}

	function debug( $tid = null ) {
		return;
	}
}

?>
