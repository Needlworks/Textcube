<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
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
define('__TEXTCUBE_LOGIN__',true);
require ROOT . '/library/includeForBlog.php';
if (false) {
	fetchConfigVal();
}

if (substr($blogURL, -1) != '/') $blogURL .= '/';
if (!isset($user['homepage']) ) $user['homepage'] = '/';
if (substr($user['homepage'], -1) != '/') $user['homepage'] .= '/';

if (isset($_GET['requestURI']))
	$_POST['requestURI'] = $_GET['requestURI'];
if (doesHaveMembership()) {
	if (!empty($_POST['requestURI']))
		$returnURL = $_POST['requestURI'];
	else
		$returnURL = $blogURL;
} else {
	$returnURL = $blogURL;
}
logout();
header("Location: $returnURL");

?>
