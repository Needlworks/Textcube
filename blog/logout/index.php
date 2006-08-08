<?php
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
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
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
