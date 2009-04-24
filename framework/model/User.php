<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// for Global Cache
global $__gCacheUserNames;
$__gCacheUserNames = array();

class Model_User {
	private static $__gCacheUserNames;
	static function getName($userid = null) {
		global $database, $__gCacheUserNames;
		if (empty($userid))
			$userid = getUserId();
		if (array_key_exists($userid, $__gCacheUserNames)) {
			return $__gCacheUserNames[$userid];
		}
		return $__gCacheUserNames[$userid] = Data_IAdapter::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = $userid");
	}
	
	static function getUserIdByName($name) {
		global $database, $__gCacheUserNames;
		if (!isset($name))
			return getUserId();
		$name = Data_IAdapter::escapeString($name);
		$userid = array_search($name, $__gCacheUserNames);
		if(!empty($userid))
			return $userid;
		$userid = Data_IAdapter::queryCell("SELECT userid FROM {$database['prefix']}Users WHERE name = '".$name."'");
		$__gCacheUserNames[$userid] = $name;
		return $userid;
	}
	
	static function getUserNamesOfBlog($blogid) {
		// TODO : Caching with global cache component. (Usually it is not changing easily.)
		global $database;
		$authorIds = Data_IAdapter::queryColumn("SELECT userid
			FROM {$database['prefix']}Privileges
			WHERE blogid = $blogid");
		$authorInfo = Data_IAdapter::queryAll("SELECT userid, name
			FROM {$database['prefix']}Users
			WHERE userid IN (".implode(",",$authorIds).")");
		return $authorInfo;
	}

	static function getBlogOwnerName($blogid) {
		global $database;
		$ownerUserId = Data_IAdapter::queryCell("SELECT userid 
			FROM {$database['prefix']}Privileges
			WHERE blogid = $blogid
				AND acl > 15");
		return Model_User::getName($ownerUserId);
	}

	static function getBlogOwner($blogid) {
		global $database;
		$ownerUserId = Data_IAdapter::queryCell("SELECT userid 
			FROM {$database['prefix']}Privileges
			WHERE blogid = $blogid
				AND acl > 15");
		return $ownerUserId;
	}

	static function getEmail($userid = null) {
		global $database;
		if (!isset($userid))
			$userid = getUserId();
		return Data_IAdapter::queryCell("SELECT loginid FROM {$database['prefix']}Users WHERE userid = $userid");
	}

	static function getUserIdByEmail($loginid = null) {
		global $database;
		$loginid = trim($loginid);
		if(!isset($loginid)) return null;
		$loginid = Data_IAdapter::escapeString($loginid);
		return Data_IAdapter::queryCell("SELECT userid FROM {$database['prefix']}Users WHERE loginid = '".$loginid."'");
	}
	
	static function getBlogs($userid = null) {
		global $database;
		if (!isset($userid))
			$userid = getUserId();
		return Data_IAdapter::queryColumn("SELECT blogid FROM {$database['prefix']}Privileges WHERE userid = $userid");
	}

	static function getOwnedBlogs($userid = null) {
		global $database;
		if (!isset($userid))
			$userid = getUserId();
		return Data_IAdapter::queryColumn("SELECT blogid FROM {$database['prefix']}Privileges WHERE userid = $userid AND acl > 15");
	}

	static function getHomepageType($userid = null) {
		global $database;
		if (!isset($userid)) 
			$userid = getUserId();
		$info = unserialize(Model_Setting::getUserSettingGlobal('userLinkInfo','',$userid));
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
		$info = unserialize(Model_Setting::getUserSettingGlobal('userLinkInfo','',$userid));
		if(is_null($info)) $info = array('type' => 'default'); 
		switch ($info['type']) {
			case "external" :
				$homepage = $info['url'];
				break;
			case "internal" :
				$homepage = getDefaultURL($info['blogid']);
				break;
			case "author" : 
				$homepage = getDefaultURL($info['blogid'])."/author/".URL::encode(Model_User::getName($userid));
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
		if (Model_Setting::setUserSettingGlobal("userLinkInfo",$homepage, $userid)) {
			return true;
		}
		return false;
	}
	
	static function confirmPassword($userid = null, $password) {
		global $database;
		if(empty($userid)) $userid = getUserId(); 
		$password = md5($password);
		return Data_IAdapter::queryExistence("SELECT userid FROM {$database['prefix']}Users WHERE userid = $userid AND password = '$password'");
	}

	static function authorName($blogid = null,$entryId){
		if( is_null($blogid) ) {
			$blogid = getBlogId();
		}

		// Read userId of entry from relation table.
		$userid = getUserIdOfEntry($blogid,$entryId);
		if(isset($userid)) {
			return Model_User::getName($userid);
		} else {
			return false;
		}
	}
	static function changeBlog(){
		global $database, $blogURL, $blog, $service;
		$blogid = getBlogId();	

		$blogList = Model_User::getBlogs();
		if (count($blogList) == 0) {
			return;
		}

		$changeBlogView = str_repeat(TAB,6)."<select id=\"blog-list\" onchange=\"location.href='{$blogURL}/owner/network/teamblog/changeBlog/?blogid='+this.value\">".CRLF;
		foreach($blogList as $info){
			$title = UTF8::lessen(Model_Setting::getBlogSettingGlobal("title",null,$info,true), 30);
			$title = ($title ? $title : _f('%1 님의 블로그',Model_User::getBlogOwnerName($info)));
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
		$email = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($email, 64));
		$nickname = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($nickname, 32));
		if ($email == '' || $nickname == '') {
			return false;
		}
		if (Data_IAdapter::queryExistence("SELECT * FROM `{$database['prefix']}Users` WHERE name = '$nickname' AND `userid` <> $userid")) {
			return false;
		} else {
			$result = Data_IAdapter::query("UPDATE `{$database['prefix']}Users` SET loginid = '$email', name = '$nickname' WHERE `userid` = $userid");		
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
	
		$loginid = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($email, 64));	
		$name = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($name, 32));
		$password = Model_User::__generatePassword();
		$authtoken = md5(Model_User::__generatePassword());
	
		if (Data_IAdapter::queryExistence("SELECT * FROM `{$database['prefix']}Users` WHERE loginid = '$loginid'")) {
			return 9;	// User already exists.
		}
	
		if (Data_IAdapter::queryCell("SELECT COUNT(*) FROM `{$database['prefix']}Users` WHERE name = '$name'")) {
			$name = $name . '.' . time();
		}

		$result = Data_IAdapter::query("INSERT INTO `{$database['prefix']}Users` (userid, loginid, password, name, created, lastLogin, host) VALUES (NULL, '$loginid', '" . md5($password) . "', '$name', UNIX_TIMESTAMP(), 0, ".getUserId().")");
		if (empty($result)) {
			return 11;
		}
		$result = Data_IAdapter::query("INSERT INTO `{$database['prefix']}UserSettings` (userid, name, value) VALUES ('".Model_User::getUserIdByEmail($loginid)."', 'AuthToken', '$authtoken')");
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
		$blogs = Model_User::getOwnedBlogs($userid);
		$sql = "UPDATE `{$database['prefix']}Comments` SET replier = NULL WHERE replier = ".$userid;
		Data_IAdapter::execute($sql);
		foreach ($blogs as $ownedBlog) {
			Model_Blog::changeOwner($ownedBlog,1); // 관리자 uid로 변경
		}
		$blogs = Model_User::getBlogs($userid);
		foreach ($blogs as $joinedBlog) {
			Model_Blog::deleteUser($joinedBlog, $userid);
		}
		Model_User::removePermanent($userid);
		return true;
	}
	
	static function removePermanent($userid) {
		global $database;
		if( Data_IAdapter::execute("DELETE FROM {$database['prefix']}UserSettings WHERE userid = '$userid' AND name = 'AuthToken' LIMIT 1") ) {
			return Data_IAdapter::execute("DELETE FROM {$database['prefix']}Users WHERE userid = $userid");
		} else {
			return false;
		}
	}
	
	static function __generatePassword() {
		return strtolower(substr(base64_encode(rand(0x10000000, 0x70000000)), 3, 8));
	}
}
?>
