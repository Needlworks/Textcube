<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define('NO_SESSION', true);
$IV = array(
	'GET' => array(
		'loginid' => array('email'),
		'key'     => array('string')
	)
);

require ROOT . '/library/includeForBlog.php';

requireStrictBlogURL();
if (false) {
	fetchConfigVal();
}
if(validateAPIKey($blogid, $_GET['loginid'], $_GET['key'])) {
	$userid = User::getUserIdByEmail($_GET['loginid']);
	if(in_array($blogid, User::getOwnedBlogs($userid))) { 
		if (file_exists(ROOT . "/cache/backup/$blogid.xml")) {
			header('Content-Type: text/xml; charset=utf-8');
			$fileHandle = fopen(ROOT . "/cache/backup/$blogid.xml", 'r+');
			$result = fread($fileHandle, filesize(ROOT . "/cache/backup/$blogid.xml"));
			echo $result;
			fclose($fileHandle);
		}
	}
}
exit;
?>
