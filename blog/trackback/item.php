<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
$IV = array(
	'POST' => array(
		'url' => array('url', 'default' => ''),
		'title' => array('string', 'default' => ''),
		'excerpt' => array('string', 'default' => ''),
		'blog_name' => array('string', 'default' => '')
	),
	'SERVER' => array(
		'CONTENT_TYPE' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
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
	if($row = DBQuery::queryRow("SELECT * FROM {$database['prefix']}Entries WHERE owner = $owner AND id = {$suri['id']} AND draft = 0 AND visibility = 3 AND acceptComment = 1"))
		sendTrackbackPing($suri['id'], "$defaultURL/".($blog['useSlogan'] ? "entry/{$row['slogan']}": $suri['id']), $url, $blog_name, $title);
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
	} else if ($result == 5) {
		printRespond(array('error' => 1, 'message' => 'URL is not exist or invalid'));
	}
}
?> 
