<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
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
require ROOT . '/library/preprocessor.php';
if(!Setting::getBlogSetting('acceptTrackbacks',1)) {
	Respond::PrintResult(array('error' => 1, 'message' => 'The entry does not accept trackback'));
	exit;	
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
/*if(!isset($suri['id'])) $suri['id'] = getEntryIdBySlogan($blogid, $suri['value']);
if(empty($suri['id'])) {
	Respond::PrintResult(array('error' => 1, 'message' => 'URL is not exist or invalid'));
	exit;
}*/
$result = receiveTrackback($blogid, $suri['id'], $title, $url, $excerpt, $blog_name);
if ($result == 0) {
	if($row = POD::queryRow("SELECT * 
		FROM {$database['prefix']}Entries
		WHERE blogid = $blogid 
			AND id = {$suri['id']} 
			AND draft = 0 
			AND visibility = 3 
			AND acceptcomment = 1"))
		sendTrackbackPing($suri['id'], "$defaultURL/".($blog['useSloganOnPost'] ? "entry/{$row['slogan']}": $suri['id']), $url, $blog_name, $title);
	Respond::ResultPage(0);
} else {
	if ($result == 1) {
		Respond::PrintResult(array('error' => 1, 'message' => 'Could not receive'));
	} else if ($result == 2) {
		Respond::PrintResult(array('error' => 1, 'message' => 'Could not receive'));
	} else if ($result == 3) {
		Respond::PrintResult(array('error' => 1, 'message' => 'The entry does not accept trackback'));
	} else if ($result == 4) {
		Respond::PrintResult(array('error' => 1, 'message' => 'already exists trackback'));
	} else if ($result == 5) {
		Respond::PrintResult(array('error' => 1, 'message' => 'URL is not exist or invalid'));
	}
}
?> 
