<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
//else
//	$_POST['requestURI'] = $_SERVER['HTTP_REFERER'];
$IV = array(
	'GET' => array(
		'requestURI' => array('string', 'default' => null)
	),
	'POST' => array(
		'requestURI' => array('string', 'default' => null)
	)
);
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}

if (substr($blogURL, -1) != '/') $blogURL .= '/';
if (substr($user['homepage'], -1) != '/') $user['homepage'] .= '/';

if (isset($_GET['requestURI']))
	$_POST['requestURI'] = $_GET['requestURI'];
if (doesHaveMembership()) {
	logout();
	if (!empty($_POST['requestURI']))
		header("Location: {$_POST['requestURI']}");
	else
		header("Location: {$user['homepage']}");
} else {
	header("Location: $blogURL");
}

?>
