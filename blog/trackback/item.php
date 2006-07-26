<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (empty($_POST['url']))
	printRespond(array('error' => 1, 'message' => 'URL is not exist'));
else
	$url = $_POST['url'];
$title = !empty($_POST['title']) ? $_POST['title'] : '';
$excerpt = !empty($_POST['excerpt']) ? $_POST['excerpt'] : '';
$blog_name = !empty($_POST['blog_name']) ? $_POST['blog_name'] : '';
if (!empty($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], 'charset') > 0) {
	$charsetPos = strpos($_SERVER["CONTENT_TYPE"], 'charset');
	$charsetArray = explode('=', substr($_SERVER["CONTENT_TYPE"], $charsetPos));
	$charset = $charsetArray[1];
	$ary[] = trim($charset);
}

$result = receiveTrackback($owner, $suri['id'], $title, $url, $excerpt, $blog_name);
if ($result == 0) {
	respondResultPage(0);
} else {
	if ($result == 1) {
		printRespond(array('error' => 1, 'message' => 'Could not receive'));
	} else if ($result == 2) {
		printRespond(array('error' => 1, 'message' => 'Could not receive'));
	} else if ($result == 3) {
		printRespond(array('error' => 1, 'message' => 'The entry is not accept trackback'));
	} else if ($result == 4) {
		printRespond(array('error' => 1, 'message' => 'already exists trackback'));
	}
}
?> 