<?php 
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function setBlogTitle($blogid, $title) {
	global $database, $blog;
	requireModel('blog.rss');
	if ($title == $blog['title'])
		return true;
	if(setBlogSetting('title', mysql_lessen($title, 255)) === false) return false;
	$blog['title'] = $title;
	clearRSS();
	return true;
}

function setBlogDescription($blogid, $description) {
	global $database, $blog;
	requireModel('blog.rss');
	if ($description == $blog['description'])
		return true;
	if(setBlogSetting('description',mysql_lessen($description, 255)) === false) return false;
	$blog['description'] = $description;
	clearRSS();
	return true;
}

function removeBlogLogo($blogid) {
	global $database, $blog;
	requireModel('blog.attachment');
	
	if(setBlogSetting('logo','') === false) return false;
	else {
		deleteAttachment($blogid, - 1, $blog['logo']);
		$blog['logo'] = '';
		return true;
	}
	return false;
}

function changeBlogLogo($blogid, $file) {
	global $database;
	global $blog;
	requireModel('blog.attachment');
	if (($attachment = addAttachment($blogid, - 1, $file)) === false) {
		return false;
	}
	if (strncmp($attachment['mime'], 'image/', 6) != 0) {
		deleteAttachment($blogid, - 1, $attachment['name']);
		return false;
	}
	if(setBlogSetting('logo',$attachment['name'])) {
		deleteAttachment($blogid, - 1, $blog['logo']);
		$blog['logo'] = $attachment['name'];
		return true;
	}
	return false;
}

function checkBlogName($name) {
	return ereg('^[[:alnum:]]+(-[[:alnum:]]+)*$', $name);
}

function setPrimaryDomain($blogid, $name) {
	global $database;
	global $service, $blog;
	requireModel('blog.rss');
	$name = mysql_lessen(strtolower(trim($name)), 32);
	if ($name == $blog['name'])
		return 0;
	if (!checkBlogName($name))
		return 1;
	if (mysql_num_rows(DBQuery::query("select * from {$database['prefix']}ReservedWords where '$name' like word")) > 0)
		return 2;
	if (mysql_num_rows(DBQuery::query("select * from {$database['prefix']}BlogSettings where name = 'name' and value = '$name'")) > 0)
		return 3;
	if(setBlogSetting('name', $name)) {
		$blog['name'] = $name;
		clearRSS();
	} else {
		return 0;
	}
}

function setSecondaryDomain($blogid, $domain) {
	global $database, $blog;
	requireModel('blog.rss');
	$domain = mysql_lessen(strtolower(trim($domain)), 64);
	if ($domain == $blog['secondaryDomain'])
		return 0;
	if (empty($domain))
		setBlogSetting('secondaryDomain','');
	else if (Validator::domain($domain)) {
		if (DBQuery::queryExistence("SELECT * FROM {$database['prefix']}BlogSettings 
			WHERE blogid <> $blogid 
				AND name = 'secondaryDomain'
				AND (value = '$domain' OR value = '" . (substr($domain, 0, 4) == 'www.' ? substr($domain, 4) : 'www.' . $domain) . "')"))
			return 1;
		setBlogSetting('secondaryDomain',$domain);
	}
	else
		return 2;
	$blog['secondaryDomain'] = $domain;
	clearRSS();
	return 0;
}

function setDefaultDomain($blogid, $default) {
	global $database, $blog;
	requireModel('blog.rss');
	$default = $default == 1 ? 1 : 0;
	if (empty($blog['secondaryDomain']) && $default == 1)
		return false;
	if ($default == $blog['defaultDomain'])
		return true;
	if(setBlogSetting('defaultDomain',$default) === false) {
		return false;
	}
	$blog['defaultDomain'] = $default;
	clearRSS();
	return true;
}

function useBlogSlogan($blogid, $useSlogan) {
	global $database, $blog;
	requireModel('blog.rss');
	$useSlogan = $useSlogan ? 1 : 0;
	if ($useSlogan == $blog['useSlogan'])
		return true;
	if(setBlogSetting('useSlogan',$useSlogan) === false) {
		return false;
	}
	$blog['useSlogan'] = $useSlogan;
	clearRSS();
	return true;
}

function publishPostEolinSyncOnRSS($blogid, $publishEolinSyncOnRSS) {
	global $database, $blog;
	requireModel('blog.rss');
	$publishEolinSyncOnRSS = $publishEolinSyncOnRSS ? 1 : 0;
	if ($publishEolinSyncOnRSS == $blog['publishEolinSyncOnRSS'])
		return true;
	if(setBlogSetting('publishEolinSyncOnRSS',$publishEolinSyncOnRSS) === false)
		return false;
	$blog['publishEolinSyncOnRSS'] = $publishEolinSyncOnRSS;
	clearRSS();
	return true;
}

function setEntriesOnRSS($blogid, $entriesOnRSS) {
	global $database, $blog;
	requireModel('blog.rss');
	if ($entriesOnRSS == $blog['entriesOnRSS'])
		return true;
	if(setBlogSetting('entriesOnRSS',$entriesOnRSS) === false) return false;
	$blog['entriesOnRSS'] = $entriesOnRSS;
	clearRSS();
	return true;
}

function setPublishWholeOnRSS($blogid, $publishWholeOnRSS) {
	global $database, $blog;
	requireModel('blog.rss');
	$publishWholeOnRSS = $publishWholeOnRSS ? 1 : 0;
	if ($publishWholeOnRSS == $blog['publishWholeOnRSS'])
		return true;
	if(setBlogSetting('publishWholeOnRSS',$publishWholeOnRSS) === false) return false;
	$blog['publishWholeOnRSS'] = $publishWholeOnRSS;
	clearRSS();
	return true;
}

function setBlogLanguage($blogid, $language, $blogLanguage) {
	global $database, $blog;
	requireModel('blog.rss');
	if (($language == $blog['language']) && ($blogLanguage == $blog['blogLanguage']))
		return true;
	$language = mysql_lessen($language, 5);
	$blogLanguage = mysql_lessen($blogLanguage, 5);
	if(setBlogSetting('language',$language) && setBlogSetting('blogLanguage',$blogLanguage)) {
		$blog['language'] = $language;
		$blog['blogLanguage'] = $blogLanguage;
		clearRSS();
		return true;
	} else return false;
}

function setGuestbook($blogid, $write, $comment) {
	global $database, $blog;
	if (!is_numeric($write) || !is_numeric($comment))
		return false;
	if(setBlogSetting('allowWriteOnGuestbook',$write) && setBlogSetting('allowWriteDblCommentOnGuestbook',$comment)) {
		return true;
	} else return false;
}

function changeSetting($blogid, $email, $nickname) {
	global $database;
	if (strcmp($email, mysql_lessen($email, 64)) != 0) return false;
	$email = mysql_tt_escape_string(mysql_lessen($email, 64));
	$nickname = mysql_tt_escape_string(mysql_lessen($nickname, 32));
	if ($email == '' || $nickname == '') {
		return false;
	}
	$sql = "UPDATE `{$database['prefix']}Users` SET loginid = '$email', name = '$nickname' WHERE `userid` = $blogid";
	$result = DBQuery::query($sql);
	if (!$result) {
		return false;
	} else {
		return true;
	}
}

function getCertificationLink($blogid) {
	global $database;
	$blogName = getBlogSetting('name');
	$users = DBQuery::queryRow("SELECT * FROM `{$database['prefix']}Users` 
		WHERE host = $blogid");
	return getBlogURL($blogName) . '/login?loginid=' . rawurlencode($users['loginid']) . '&password=' . rawurlencode($users['password']) . '&requestURI=' . rawurlencode(getBlogURL($blogName) . "/owner/setting/account?password=" . rawurlencode($users['password']));
}

function addUser($email, $name) {
	global $database, $service, $user, $blog;
	if (empty($email))
		return 1;
	if (!ereg('^[^@]+@([[:alnum:]]+(-[[:alnum:]]+)*\.)+[[:alnum:]]+(-[[:alnum:]]+)*$', $email))
		return 2;

	if (strcmp($email, mysql_lessen($email, 64)) != 0) return 11;

	$loginid = mysql_tt_escape_string(mysql_lessen($email, 64));	
	$name = mysql_tt_escape_string(mysql_lessen($name, 32));
	$password = generatePassword();

	$result = DBQuery::query("SELECT * FROM `{$database['prefix']}Users` WHERE loginid = '$loginid'");
	if ($result && (mysql_num_rows($result) > 0)) {
		return 5;
	}

	$result = DBQuery::query("INSERT INTO `{$database['prefix']}Users` (userid, loginid, password, name, created, lastLogin, host) VALUES (NULL, '$loginid', '" . md5($password) . "', '$name', UNIX_TIMESTAMP(), 0, ".getUserId().")");
	if (!$result || (mysql_affected_rows() == 0)) {
		return 11;
	}
	return true;
}

function addBlog($blogid, $userid, $identify) {
	global $database, $service, $blogURL, $hostURL, $user, $blog;

	if(empty($userid)) {
		$userid = 1; // If no userid, choose the service administrator.
	} else {
		if(!DBQuery::queryExistence("SELECT userid
			FROM {$database['prefix']}Users
			WHERE userid = ".$userid)) return 3; // 3: No user exists with specific userid
	}

	if(!empty($blogid)) { // If blogid,
		if(!DBQuery::queryExistence("SELECT blogid
			FROM {$database['prefix']}BlogSettings
			WHERE blogid = ".$blogid)) {
			return 2; // 2: No blog exists with specific blogid
		}
		// Thus, blog and user exists. Now combine both.
		$result = DBQuery::query("INSERT INTO `{$database['prefix']}Teamblog` 
			(blogid,userid,acl,created,lastLogin) 
			VALUES('$blogid', '$userid', '0', UNIX_TIMESTAMP(), '0')");
		return $result;
	} else { // If no blogid, create a new blog.
		$email = getUserEmail($userid);
		$password = DBQuery::queryCell("SELECT password
			FROM {$database['prefix']}Users
			WHERE userid = ".$userid);
		$blogName = getBlogName($blogid);
		if (empty($email) || empty($identify))
			return 1; // Not enough information to create blog.
		if (!ereg('^[^@]+@([[:alnum:]]+(-[[:alnum:]]+)*\.)+[[:alnum:]]+(-[[:alnum:]]+)*$', $email))
			return 2; // Wrong E-mail address
		if (!ereg('^[[:alnum:]]+$', $identify))
			return 4; // Wrong Blog name
		if (empty($name))
			$name = User::getName($userid);

		if (strcmp($email, mysql_lessen($email, 64)) != 0) return 11;

		$loginid = mysql_tt_escape_string(mysql_lessen($email, 64));	
		$name = mysql_tt_escape_string(mysql_lessen($name, 32));
		$identify = mysql_tt_escape_string(mysql_lessen($identify, 32));
		$password = generatePassword();

		$blogName = $identify;

		$result = DBQuery::query("SELECT * 
			FROM `{$database['prefix']}ReservedWords` 
			WHERE word = '$blogName'");
		if ($result && (mysql_num_rows($result) > 0)) {
			return 60;	// Reserved blog name.
		}
		$result = DBQuery::query("SELECT value 
			FROM `{$database['prefix']}BlogSettings` 
			WHERE name = 'name' AND value = '$blogName'");
		if ($result && (mysql_num_rows($result) > 0)) {
			return 61; // Same blogname is already exists.
		}
		$blogid = DBQuery::queryCell("SELECT max(blogid)
			FROM `{$database['prefix']}BlogSettings`") + 1;
		$baseTimezone = mysql_tt_escape_string($service['timezone']);
		$basicInformation = array(
			'name'         => $identify,
			'defaultDomain'            => 0,
			'title'                    => '',
			'description'              => '',
			'logo'                     => '',
			'logoLabel'                => '',
			'logoWidth'                => 0,
			'logoHeight'               => 0,
			'useSlogan'                => 1,
			'entriesOnPage'            => 10,
			'entriesOnList'            => 10,
			'entriesOnRSS'             => 10,
			'publishWholeOnRSS'        => 1,
			'publishEolinSyncOnRSS'    => 0,
			'allowWriteOnGuestbook'    => 1,
			'allowWriteDblCommentOnGuestbook' => 1,
			'language'     => $service['language'],
			'blogLanguage' => $service['language'],
			'timezone'     => $baseTimezone);
		$isFalse = false;
		foreach($basicInformation as $fieldname => $fieldvalue){
			if(setBlogSetting($fieldname,$fieldvalue,$blogid) === false) {
				$isFalse = true;
			}
		}
		if($isFalse == true) {
			DBQuery::query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `blogid` = $blogid");
			return 12;
		}
	
		$result = DBQuery::query("INSERT INTO `{$database['prefix']}SkinSettings` 
			(blogid, skin) 
			VALUES ($blogid, '{$service['skin']}')");
		if (!$result || (mysql_affected_rows() == 0)) {
			DBQuery::query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `blogid` = $blogid");
			return 13;
		}
		$result = DBQuery::query("INSERT INTO `{$database['prefix']}FeedSettings` 
			(blogid) VALUES ($blogid)");
		if (!$result || (mysql_affected_rows() == 0)) {
			DBQuery::query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `blogid` = $blogid");
			DBQuery::query("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `blogid` = $blogid");
			return 62;
		}
		
		$result = DBQuery::query("INSERT INTO `{$database['prefix']}FeedGroups` 
			(blogid, id) 
			VALUES ($blogid, 0)");
		if (!$result || (mysql_affected_rows() == 0)) {
			DBQuery::query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `blogid` = $blogid");
			DBQuery::query("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `blogid` = $blogid");
			DBQuery::query("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `blogid` = $blogid");
			return 62;
		}
	
		$result = DBQuery::query("INSERT INTO `{$database['prefix']}Teamblog` 
			VALUES('".$blogid."', '".$userid."', '0', UNIX_TIMESTAMP(), '0')");
		if (!$result || (mysql_affected_rows() == 0)) {
			header( 'x-debug: ' . mysql_errno() );
			DBQuery::query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `blogid` = $blogid");
			DBQuery::query("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `blogid` = $blogid");
			DBQuery::query("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `blogid` = $blogid");
			DBQuery::query("DELETE FROM `{$database['prefix']}FeedGroups` WHERE `blogid` = $blogid");		
			return 20;
		}
	}
	return true;
}

function getInvited($userid) {
	global $database;
	return DBQuery::queryAll("SELECT *
		FROM {$database['prefix']}Users
		WHERE `host` = '".$userid."'
		ORDER BY created ASC");
}

function getBlogName($blogid) {
	global $database;
	return DBQuery::queryCell("SELECT value
		FROM {$database['prefix']}BlogSettings
		WHERE blogid = $blogid AND name = 'name'");
}

function sendInvitationMail($blogid, $userid, $name, $comment, $senderName, $senderEmail) {
	global $database, $service, $blogURL, $hostURL, $user, $blog;
	if(empty($blogid)) {
		$blogid = DBQuery::queryCell("SELECT max(blogid)
			FROM {$database['prefix']}BlogSettings"); // If no blogid, get the latest created blogid.
	}
	$email = getUserEmail($userid);
	$password = DBQuery::queryCell("SELECT password
		FROM {$database['prefix']}Users
		WHERE userid = ".$userid);
	$blogName = getBlogName($blogid);

	if (empty($email))
		return 1;
	if (!ereg('^[^@]+@([[:alnum:]]+(-[[:alnum:]]+)*\.)+[[:alnum:]]+(-[[:alnum:]]+)*$', $email))
		return 2;
	if (empty($name))
		$name = User::getName($userid);

	if (strcmp($email, mysql_lessen($email, 64)) != 0) return 11;

	$loginid = mysql_tt_escape_string(mysql_lessen($email, 64));	
	$name = mysql_tt_escape_string(mysql_lessen($name, 32));

	$headers = 'From: ' . encodeMail($senderName) . '<' . $senderEmail . ">\n" . 'X-Mailer: ' . TEXTCUBE_NAME . "\n" . "MIME-Version: 1.0\nContent-Type: text/html; charset=utf-8\n";
	if (empty($name))
		$subject = _textf('귀하를 %1님이 초대합니다', $senderName);
	else
		$subject = _textf('%1님을 %2님이 초대합니다', $name, $senderName);
	$message = file_get_contents(ROOT . "/style/letter/letter.html");
	$message = str_replace('[##_title_##]', _text('초대장'), $message);
	$message = str_replace('[##_content_##]', $comment, $message);
	$message = str_replace('[##_images_##]', "$hostURL{$service['path']}/style/letter", $message);
	$message = str_replace('[##_link_##]', getBlogURL($blogName) . '/login?loginid=' . rawurlencode($email) . '&password=' . rawurlencode($password) . '&requestURI=' . rawurlencode(getBlogURL($blogName) . "/owner/setting/account?password=" . rawurlencode($password)), $message);
	$message = str_replace('[##_go_blog_##]', getBlogURL($blogName), $message);
	$message = str_replace('[##_link_title_##]', _text('블로그 바로가기'), $message);
	if (empty($name)) {
		$message = str_replace('[##_to_##]', '', $message);
	} else {
		$message = str_replace('[##_to_##]', _text('받는 사람') . ': ' . $name, $message);
	}
	$message = str_replace('[##_sender_##]', _text('보내는 사람') . ': ' . $senderName, $message);
	if (!mail($email, encodeMail($subject), $message, $headers)) {
		return 14;
	} else {
		return 15;
	}
}


function cancelInvite($userid) {
	global $database;
	if (DBQuery::queryCell("SELECT count(*) FROM `{$database['prefix']}Users` WHERE `userid` = $userid AND `lastLogin` = 0") == 0)
		return false;
	if (DBQuery::queryCell("SELECT count(*) FROM `{$database['prefix']}Users` WHERE `userid` = $userid AND `host` = ".getBlogId()) === 0)
		return false;
	if (DBQuery::execute("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $userid")) {
		if (DBQuery::execute("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `blogid` = $userid")) {
			if (DBQuery::execute("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `blogid` = $userid")) {
				if (DBQuery::execute("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `blogid` = $userid")) {
					if(DBQuery::execute("DELETE FROM `{$database['prefix']}Teamblog` WHERE userid='$userid'")) {
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function changePassword($blogid, $pwd, $prevPwd) {
	global $database;
	if (!strlen($pwd) || !strlen($prevPwd))
		return false;
	if ((strlen($prevPwd) == 32) && preg_match('/[0-9a-f]/i', $prevPwd))
		$secret = '(`password` = \'' . md5($prevPwd) . "' OR `password` = '$prevPwd')";
	else
		$secret = '`password` = \'' . md5($prevPwd) . '\'';
	$count = DBQuery::queryCell("select count(*) from {$database['prefix']}Users where userid = $blogid and $secret");
	if ($count == 0)
		return false;
	$pwd = md5($pwd);
	$sql = "UPDATE `{$database['prefix']}Users` SET password = '$pwd' WHERE `userid` = $blogid";
	return DBQuery::execute($sql);
}

function deleteUser($userid){
	global $database;

	DBQuery::execute("UPDATE `{$database['prefix']}Entries` 
		SET userid = ".getBlogId()." 
		WHERE blogid = ".getBlogId()." AND userid = ".$userid);

	if(DBQuery::execute("DELETE FROM `{$database['prefix']}Teamblog` WHERE blogid = ".getBlogId()." and userid='$userid'")){
		$En = DBQuery::queryCell("SELECT userid FROM `{$database['prefix']}Teamblog` WHERE userid = '$userid'");
		if(empty($En)){
			@DBQuery::execute("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $userid");
			@DBQuery::execute("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `blogid` = $userid");
			@DBQuery::execute("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `blogid` = $userid");
			@DBQuery::execute("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `blogid` = $userid");
		}
		return true;
	} else {
		return false;
	}
}
?>
