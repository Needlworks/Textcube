<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* This component contains 'User', 'Blog' and 'Transaction' class. 
   NOTE : Classes described below are actually not object. Usually they are static.*/

class Model_Blog {
	/*@static@*/
	function changeOwner($blogid,$userid) {
		global $database;
		Data_IAdapter::execute("UPDATE `{$database['prefix']}Privileges` SET acl = 3 WHERE blogid = ".$blogid." and acl = " . BITWISE_OWNER);
	
		$acl = Data_IAdapter::queryCell("SELECT acl FROM {$database['prefix']}Privileges WHERE blogid='$blogid' and userid='$userid'");
	
		if( $acl === null ) { // If there is no ACL, add user into the blog.
			Data_IAdapter::query("INSERT INTO `{$database['prefix']}Privileges`  
				VALUES('$blogid', '$userid', '".BITWISE_OWNER."', UNIX_TIMESTAMP(), '0')");
		} else {
			Data_IAdapter::execute("UPDATE `{$database['prefix']}Privileges` SET acl = ".BITWISE_OWNER." 
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
		
		$isUserExists = Model_User::getUserIdByEmail($email);
		if(empty($isUserExists)) { // If user is not exist
			Model_User::add($email,$name);
		}
		$userid = Model_User::getUserIdByEmail($email);
		$result = addBlog(getBlogId(), $userid, null);
		if($result === true) {
			return sendInvitationMail(getBlogId(), $userid, Model_User::getName($userid), $comment, $senderName, $senderEmail);
		}
		return $result;
	}

	/*@static@*/
	function deleteUser($blogid = null, $userid, $clean = true) {
		global $database;
		if ($blogid == null) {
			$blogid = getBlogId();
		}
		Data_IAdapter::execute("UPDATE `{$database['prefix']}Entries` 
			SET userid = ".Model_User::getBlogOwner($blogid)." 
			WHERE blogid = ".$blogid." AND userid = ".$userid);
	
		// Delete ACL relation.
		if(!Data_IAdapter::execute("DELETE FROM `{$database['prefix']}Privileges` WHERE blogid='$blogid' and userid='$userid'"))
			return false;
		// And if there is no blog related to the specific user, delete user.
		if($clean && !Data_IAdapter::queryAll("SELECT * FROM `{$database['prefix']}Privileges` WHERE userid = '$userid'")) {
			Model_User::removePermanent($userid);
		}
		return true;
	}
	
	/*@static@*/
	function changeACLofUser($blogid, $userid, $ACLtype, $switch) {  // Change user priviledge on the blog.
		global $database;
		if(empty($ACLtype) || empty($userid))
			return false;
		$acl = Data_IAdapter::queryCell("SELECT acl
				FROM {$database['prefix']}Privileges 
				WHERE blogid='$blogid' and userid='$userid'");
		if( $acl === null ) { // If there is no ACL, add user into the blog.
			$name = Model_User::getName($userid);
			Data_IAdapter::query("INSERT INTO `{$database['prefix']}Privileges`  
					VALUES('$blogid', '$userid', '0', UNIX_TIMESTAMP(), '0')");
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
		return Data_IAdapter::execute("UPDATE `{$database['prefix']}Privileges` 
			SET acl = ".$acl." 
			WHERE blogid = ".$blogid." and userid = ".$userid);
	}
}
?>
