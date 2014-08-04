<?php
/// Copyright (c) 2004-2014, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

if (!array_key_exists('SERVER_SOFTWARE', $_SERVER)) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

$SERVER_ENGINE = strtok($_SERVER['SERVER_SOFTWARE'], '/');
if ($SERVER_ENGINE != 'Google App Engine' && $SERVER_ENGINE != 'Development') {
	header("HTTP/1.0 404 Not Found");
	exit;
}

if (strtok($_SERVER["REQUEST_URI"], '/?') == 'setup.php') {
	// setup.php does not supported.
	header("HTTP/1.0 404 Not Found");
	exit();
}

use google\appengine\api\cloud_storage\CloudStorageTools;

define('__TEXTCUBE_GAE__', true);

if ($SERVER_ENGINE == 'Google App Engine') {
	define('__TEXTCUBE_CACHE_DIR__', 'gs://' . $_SERVER['blog_fs_bucket'] . '/cache');
	define('__TEXTCUBE_ATTACH_DIR__', 'gs://' . $_SERVER['blog_fs_bucket'] . '/attach');
} else {
	define('__TEXTCUBE__LOCAL_DEVELOPMENT__', true);
}
if (!array_key_exists('blog_fs_bucket', $_SERVER)) {
	syslog('Missing a blog_fs_bucket env variable in app.yaml');
	header("HTTP/1.0 404 Not Found");
	exit();
}

// Modify SCRIPT_NAME for other codes.
$_SERVER["SCRIPT_NAME"] = str_replace('gae.php', '', $_SERVER["SCRIPT_NAME"]);

// Handles $blogURL/attach/... for attachment files.
if (substr($_SERVER["REQUEST_URI"], 0, 8) == '/attach/') {
	$requestFilename = strtok(substr($_SERVER["REQUEST_URI"], 7), '?#');
	if (file_exists(__TEXTCUBE_ATTACH_DIR__ . $requestFilename)) {
		require 'library/function/file.php';
		$option = [
				'content_type' => getMIMEType('', $_SERVER["REQUEST_URI"])
		];
		CloudStorageTools::serve(__TEXTCUBE_ATTACH_DIR__ . $requestFilename, $option);
		exit();
	}
}
	
require_once ('rewrite.php');
?>
