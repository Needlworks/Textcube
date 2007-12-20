<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
function addTeamUser($email, $name, $comment, $senderName, $senderEmail) {
	requireModel('blog.user');
	requireModel('blog.blogSetting');
	global $database,$service,$blogURL,$hostURL,$user,$blog;

	$blogid = getBlogId();
	if(empty($email))
		return 1;
	if(!preg_match('/^[^@]+@([-a-zA-Z0-9]+\.)+[-a-zA-Z0-9]+$/',$email))
		return 2;
	
	$isUserExists = getUserIdByEmail($email);
	if(empty($isUserExists)) { // If user is not exist
		addUser($email,$name);
	}
	$userid = getUserIdByEmail($email);
	$result = addBlog(getBlogId(), $userid, null);
	if($result == true) {
		sendInvitationMail(getBlogId(), $userid, User::getName($userid), $comment, $senderName, $senderEmail);
		return true;
	} else {
		return $result;
	}
	return false;
}

function cancelTeamblogInvite($userid) {
	global $database;

	$blogId = getBlogId();
	// If there is posts, cannot cancel invitation.
	if( 0 != POD::queryCell("SELECT count(*) FROM {$database['prefix']}Entries 
		WHERE blogid = $blogid AND userid = $userid")) {
		return false;
	}
	// Delete ACL relation.
	if(!POD::execute("DELETE FROM `{$database['prefix']}Teamblog` WHERE blogid='$blogid' and userid='$userid'"))
		return false;
	// And if there is no blog related to the specific user, delete user.
	if(POD::queryAll("SELECT * FROM `{$database['prefix']}Teamblog` WHERE userid = '$userid'")) {
		deleteUser($userid);
	}
	return true;
}

function changeACLonBlog($blogid, $ACLtype, $userid, $switch) {  // Change user priviledge on the blog.
	global $database;
	if(empty($ACLtype) || empty($userid))
		return false;

	$acl = POD::queryCell("SELECT acl
			FROM {$database['prefix']}Teamblog 
			WHERE blogid='$blogid' and userid='$userid'");

	if( $acl === null ) { // If there is no ACL, add user into the blog.
		$name = User::getName($userid);
		POD::query("INSERT INTO `{$database['prefix']}Teamblog`  
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

	$sql = "UPDATE `{$database['prefix']}Teamblog` 
		SET acl = ".$acl." 
		WHERE blogid = ".$blogid." and userid = ".$userid;
	return POD::execute($sql);
}

function deleteTeamblogUser($userid) {
	global $database;

	POD::execute("UPDATE `{$database['prefix']}Entries` 
		SET userid = ".getBlogId()." 
		WHERE blogid = ".getBlogId()." AND userid = ".$userid);

	if(POD::execute("DELETE FROM `{$database['prefix']}Teamblog` WHERE blogid = ".getBlogId()." and userid='$userid'")) {
		return true;
	} else {
		return false;
	}
}
?>
