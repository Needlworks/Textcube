<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (isset($_GET['requestURI']))
	$_POST['requestURI'] = $_GET['requestURI'];
//else
//	$_POST['requestURI'] = $_SERVER['HTTP_REFERER'];
$IV = array(
	'POST' => array(
		'requestURI' => array('string')
	)
);
if(!Validator::validate($IV))
	respondNotFoundPage();
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
