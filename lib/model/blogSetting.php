<?

function setBlogTitle($owner, $title) {
	global $database;
	global $blog;
	if ($title == $blog['title'])
		return true;
	mysql_query("update {$database['prefix']}BlogSettings set title = '" . mysql_escape_string($title) . "' where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	$blog['title'] = $title;
	clearRSS();
	return true;
}

function setBlogDescription($owner, $description) {
	global $database;
	global $blog;
	if ($description == $blog['description'])
		return true;
	mysql_query("update {$database['prefix']}BlogSettings set description = '" . mysql_escape_string($description) . "' where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	$blog['description'] = $description;
	clearRSS();
	return true;
}

function removeBlogLogo($owner) {
	global $database, $blog;

	$result = mysql_query("update {$database['prefix']}BlogSettings set logo = '' where owner = $owner");
	if ($result && (mysql_affected_rows() == 1)) {
		deleteAttachment($owner, - 1, $blog['logo']);
		$blog['logo'] = '';
		return true;
	}
	return false;
}

function changeBlogLogo($owner, $file) {
	global $database;
	global $blog;
	if (($attachment = addAttachment($owner, - 1, $file)) === false) {
		return false;
	}
	if (strncmp($attachment['mime'], 'image/', 6) != 0) {
		deleteAttachment($owner, - 1, $attachment['name']);
		return false;
	}
	$result = mysql_query("update {$database['prefix']}BlogSettings set logo = '{$attachment['name']}' where owner = $owner");
	if ($result && (mysql_affected_rows() == 1)) {
		deleteAttachment($owner, - 1, $blog['logo']);
		$blog['logo'] = $attachment['name'];
		return true;
	}
	return false;
}

function checkBlogName($name) {
	return ereg('^[[:alnum:]]+$', $name);
}

function setPrimaryDomain($owner, $name) {
	global $database;
	global $service, $blog;
	$name = strtolower($name);
	if ($name == $blog['name'])
		return true;
	if (!checkBlogName($name))
		return false;
	if (mysql_num_rows(mysql_query("select * from {$database['prefix']}ReservedWords where '$name' like word")) > 0)
		return false;
	if (mysql_num_rows(mysql_query("select * from {$database['prefix']}BlogSettings where name = '$name'")) > 0)
		return false;
	mysql_query("update {$database['prefix']}BlogSettings set name = '$name' where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	$blog['name'] = $name;
	clearRSS();
	return true;
}

function setSecondaryDomain($owner, $domain) {
	global $database;
	global $blog;
	$domain = strtolower($domain);
	if ($domain == $blog['secondaryDomain'])
		return true;
	if (!checkDomainName($domain))
		return false;
	mysql_query("update {$database['prefix']}BlogSettings set secondaryDomain = '$domain' where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	$blog['secondaryDomain'] = $domain;
	clearRSS();
	return true;
}

function setDefaultDomain($owner, $default) {
	global $database;
	global $blog;
	$default = $default == 1 ? 1 : 0;
	if ($default == $blog['defaultDomain'])
		return true;
	mysql_query("update {$database['prefix']}BlogSettings set defaultDomain = $default where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	$blog['defaultDomain'] = $default;
	clearRSS();
	return true;
}

function useBlogSlogan($owner, $useSlogan) {
	global $database;
	global $blog;
	$useSlogan = $useSlogan ? 1 : 0;
	if ($useSlogan == $blog['useSlogan'])
		return true;
	mysql_query("update {$database['prefix']}BlogSettings set useSlogan = $useSlogan where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	$blog['useSlogan'] = $useSlogan;
	clearRSS();
	return true;
}

function publishPostEolinSyncOnRSS($owner, $publishEolinSyncOnRSS) {
	global $database;
	global $blog;
	$publishEolinSyncOnRSS = $publishEolinSyncOnRSS ? 1 : 0;
	if ($publishEolinSyncOnRSS == $blog['publishEolinSyncOnRSS'])
		return true;
	mysql_query("update {$database['prefix']}BlogSettings set publishEolinSyncOnRSS = $publishEolinSyncOnRSS where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	$blog['publishEolinSyncOnRSS'] = $publishEolinSyncOnRSS;
	clearRSS();
	return true;
}

function setEntriesOnRSS($owner, $entriesOnRSS) {
	global $database;
	global $blog;
	if ($entriesOnRSS == $blog['entriesOnRSS'])
		return true;
	mysql_query("update {$database['prefix']}BlogSettings set entriesOnRSS = $entriesOnRSS where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	$blog['entriesOnRSS'] = $entriesOnRSS;
	clearRSS();
	return true;
}

function setPublishWholeOnRSS($owner, $publishWholeOnRSS) {
	global $database;
	global $blog;
	$publishWholeOnRSS = $publishWholeOnRSS ? 1 : 0;
	if ($publishWholeOnRSS == $blog['publishWholeOnRSS'])
		return true;
	mysql_query("update {$database['prefix']}BlogSettings set publishWholeOnRSS = $publishWholeOnRSS where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	$blog['publishWholeOnRSS'] = $publishWholeOnRSS;
	clearRSS();
	return true;
}

function setBlogLanguage($owner, $language) {
	global $database;
	global $blog;
	if ($language == $blog['language'])
		return true;
	mysql_query("update {$database['prefix']}BlogSettings set language = '$language' where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	$blog['language'] = $language;
	clearRSS();
	return true;
}

function setGuestbook($owner, $write, $comment) {
	global $database;
	global $blog;
	if (!is_numeric($write) || !is_numeric($comment))
		return false;
	mysql_query("update {$database['prefix']}BlogSettings set allowWriteOnGuestbook = $write, allowWriteDoubleCommentOnGuestbook = $comment where owner = $owner");
	if (mysql_affected_rows() != 1)
		return false;
	return true;
}

function changeSetting($owner, $email, $nickname) {
	global $database;
	$email = mysql_escape_string($email);
	$nickname = mysql_escape_string($nickname);
	if ($email == '' || $nickname == '') {
		return false;
	}
	$sql = "UPDATE `{$database['prefix']}Users` SET loginid = '$email', name = '$nickname' WHERE `userid` = $owner";
	$result = mysql_query($sql);
	if (!$result) {
		return false;
	} else {
		return true;
	}
}

function getCertificationLink($owner) {
	global $database;
	$blogSettings = fetchQueryRow("SELECT * FROM `{$database['prefix']}BlogSettings` WHERE owner=$owner");
	$users = fetchQueryRow("SELECT * FROM `{$database['prefix']}Users` WHERE userid=$owner");
	return getBlogURL($blogSettings['name']) . '/login?loginid=' . rawurlencode($users['loginid']) . '&password=' . rawurlencode($users['password']) . '&requestURI=' . rawurlencode(getBlogURL($blogSettings['name']) . "/owner/setting/account?password=" . rawurlencode($users['password']));
}

function addUser($email, $name, $identify, $comment, $senderName, $senderEmail) {
	global $database, $service, $blogURL, $hostURL, $user, $blog, $owner;
	if (empty($email) || empty($identify))
		return 1;
	if (!ereg('^[^@]+@([[:alnum:]]+(-[[:alnum:]]+)*\.)+[[:alnum:]]+(-[[:alnum:]]+)*$', $email))
		return 2;
	if (!ereg('^[[:alnum:]]+$', $identify))
		return 4;
	if (empty($name))
		$name = $identify;
	$loginid = mysql_escape_string($email);
	$password = generatePassword();
	$name = mysql_escape_string($name);
	$blogName = $identify;
	$result = mysql_query("SELECT * FROM `{$database['prefix']}Users` WHERE loginid = '$loginid'");
	if ($result && (mysql_num_rows($result) > 0)) {
		return 5;
	}
	$result = mysql_query("SELECT * FROM `{$database['prefix']}ReservedWords` WHERE word = '$blogName'");
	if ($result && (mysql_num_rows($result) > 0)) {
		return 60;
	}
	$result = mysql_query("SELECT * FROM `{$database['prefix']}BlogSettings` WHERE name = '$blogName'");
	if ($result && (mysql_num_rows($result) > 0)) {
		return 61;
	}
	$result = mysql_query("INSERT INTO `{$database['prefix']}Users` VALUES ('', '$loginid', '" . md5($password) . "', '$name', UNIX_TIMESTAMP(), 0, $owner)");
	if (!$result || (mysql_affected_rows() == 0)) {
		return 11;
	}
	$id = mysql_insert_id();
	$result = mysql_query("INSERT INTO `{$database['prefix']}BlogSettings` (owner, name) VALUES ($id, '$identify')");
	if (!$result || (mysql_affected_rows() == 0)) {
		mysql_query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
		return 12;
	}
	$result = mysql_query("INSERT INTO `{$database['prefix']}SkinSettings` (owner, skin) VALUES ($id, '{$service['skin']}')");
	if (!$result || (mysql_affected_rows() == 0)) {
		mysql_query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
		mysql_query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
		return 13;
	}
	$result = mysql_query("INSERT INTO `{$database['prefix']}FeedSettings` (owner) VALUES ($id)");
	if (!$result || (mysql_affected_rows() == 0)) {
		mysql_query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
		mysql_query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
		mysql_query("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $id");
		return 62;
	}
	$result = mysql_query("INSERT INTO `{$database['prefix']}FeedGroups` (owner, id) VALUES ($id, 0)");
	if (!$result || (mysql_affected_rows() == 0)) {
		mysql_query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
		mysql_query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
		mysql_query("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $id");
		mysql_query("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `owner` = $id");
		return 62;
	}
	$headers = 'From: ' . encodeMail($senderName) . '<' . $senderEmail . ">\n" . 'X-Mailer: ' . TATTERTOOLS_NAME . "\n" . "MIME-Version: 1.0\nContent-Type: text/html; charset=utf-8\n";
	if (empty($name))
		$subject = _f('귀하를 %1님이 초대합니다', $senderName);
	else
		$subject = _f('%1님을 %2님이 초대합니다', $name, $senderName);
	$message = file_get_contents(ROOT . "/style/letter/letter.html");
	$message = str_replace('[##_title_##]', _t('초대장'), $message);
	$message = str_replace('[##_content_##]', $comment, $message);
	$message = str_replace('[##_images_##]', "$hostURL{$service['path']}/style/letter", $message);
	$message = str_replace('[##_link_##]', getBlogURL($blogName) . '/login?loginid=' . rawurlencode($email) . '&password=' . rawurlencode($password) . '&requestURI=' . rawurlencode(getBlogURL($blogName) . "/owner/setting/account?password=" . rawurlencode($password)), $message);
	$message = str_replace('[##_go_blog_##]', getBlogURL($blogName), $message);
	$message = str_replace('[##_link_title_##]', _t('블로그 바로가기'), $message);
	if (empty($name)) {
		$message = str_replace('[##_to_##]', '', $message);
	} else {
		$message = str_replace('[##_to_##]', _t('받는 사람') . ': ' . $name, $message);
	}
	$message = str_replace('[##_sender_##]', _t('보내는 사람') . ': ' . $senderName, $message);
	if (!mail($email, encodeMail($subject), $message, $headers)) {
		mysql_query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
		mysql_query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
		mysql_query("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $id");
		mysql_query("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `owner` = $id");
		return 14;
	} else {
		return 15;
	}
}

function getInvited($owner) {
	global $database;
	return fetchQueryAll("SELECT _users.*,_blogSettings.name AS blogName FROM `{$database['prefix']}Users` AS _users LEFT JOIN `{$database['prefix']}BlogSettings` AS _blogSettings ON _users.userid = _blogSettings.owner WHERE `host` = $owner ORDER BY created ASC");
}

function cancelInvite($userid) {
	global $owner, $database;
	if (fetchQueryCell("SELECT count(*) FROM `{$database['prefix']}Users` WHERE `userid` = $userid AND `lastLogin` = 0") == 0)
		return false;
	if (fetchQueryCell("SELECT count(*) FROM `{$database['prefix']}Users` WHERE `userid` = $userid AND `host` = $owner") === 0)
		return false;
	if (executeQuery("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $userid")) {
		if (executeQuery("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $userid")) {
			if (executeQuery("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $userid")) {
				if (executeQuery("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `owner` = $userid")) {
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
}

function changePassword($owner, $pwd, $prevPwd) {
	global $database;
	if (!strlen($pwd) || !strlen($prevPwd))
		return false;
	if ((strlen($prevPwd) == 32) && preg_match('/[0-9a-f]/i', $prevPwd))
		$secret = '(`password` = \'' . md5($prevPwd) . "' OR `password` = '$prevPwd')";
	else
		$secret = '`password` = \'' . md5($prevPwd) . '\'';
	$count = fetchQueryCell("select count(*) from {$database['prefix']}Users where userid = $owner and $secret");
	if ($count == 0)
		return false;
	$pwd = md5($pwd);
	$sql = "UPDATE `{$database['prefix']}Users` SET password = '$pwd' WHERE `userid` = $owner";
	return executeQuery($sql);
}
?>
