<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'GET' => array(
		'requestURI' => array('string', 'default' => null)
	),
	'POST' => array(
		'requestURI' => array('string', 'default' => null)
	)
);
define('__TEXTCUBE_LOGIN__',true);
require ROOT . '/library/preprocessor.php';
$ctx = Model_Context::getInstance();
$userURL = $ctx->getProperty('user.homepage');
if (substr($context->getProperty('uri.blog'), -1) != '/') {
	$context->setProperty('uri.blog', $context->getProperty('uri.blog').'/');
}
if (!isset($userURL) ) $userURL = '/';
if (substr($userURL, -1) != '/') $userURL .= '/';

if (isset($_GET['requestURI']))
	$_POST['requestURI'] = $_GET['requestURI'];
if (doesHaveMembership()) {
	if (!empty($_POST['requestURI']))
		$returnURL = $_POST['requestURI'];
	else
		$returnURL = $context->getProperty('uri.blog');
} else {
	$returnURL = $context->getProperty('uri.blog');
}
logout();
header("Location: $returnURL");
?>
