<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
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
if(!Setting::getBlogSettingGlobal('acceptTrackbacks',0)) {
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
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setQualifier("blogid","eq",$blogid);
    $pool->setQualifier("id","eq",$context->getProperty("suri.id"));
    $pool->setQualifier("draft","eq",0);
    $pool->setQualifier("visibility","eq",3);
    $pool->setQualifier("acceptcomment","eq",1);
    if($row = $pool->getRow()) {
        sendTrackbackPing($suri['id'], $context->getProperty('uri.default') . "/" . ($context->getProperty('blog.useSloganOnPost') ? "entry/{$row['slogan']}" : $suri['id']), $url, $blog_name, $title);
    }
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
