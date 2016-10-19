<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define('__TEXTCUBE_LINE__',true);

$IV = array(
	'GET' => array(
		'key' => array('string','default'=>''),
		'mode' => array('string','default'=>'url'),
		'content' => array('string','default'=>''),
		'category' => array('string','default'=>'public'),
		'page' => array('int',1,'default'=>'')
	),
	'POST' => array(
		'key' => array('string','default'=>''),
		'mode' => array('string','default'=>'url'),
		'category' => array('string','default'=>'public'),
		'content' => array('string','default'=>'')
	)
);

require ROOT . '/library/preprocessor.php';

if(!empty($_POST['content'])) {
	if(!empty($_POST['key'])) {
		$key = $_POST['key'];
	} else {
		$key = null;
	}
	$content = $_POST['content'];
	$category = $_POST['category'];
	$mode = $_POST['mode'];
} else {
	$key = $_GET['key'];
	$content = $_GET['content'];
	$category = $_GET['category'];
	$mode = $_GET['mode'];
}

$lineobj = Model_Line::getInstance();
$lineobj->reset();
// If line comes.
if(!empty($content)) {
	$password = Setting::getBlogSetting('LinePassword', null, true);
	if(($password === $key) || doesHaveOwnership()) {
		$lineobj->content = $content;
		$lineobj->category = $category;
		$result = $lineobj->add();
		fireEvent('AddLine',$result, $lineobj);
		$cache = pageCache::getInstance();
		$cache->name = 'linesATOM';
		$cache->purge();
		$cache->reset();
		$cache->name = 'linesRSS';
		$cache->purge();
		if($mode == 'url') $lineobj->showResult($result);
		else {
			Respond::ResultPage(0);
		}
	}
} else {
	/// Prints public lines
//	$lineobj->setFilter(array('created','bigger',(Timestamp::getUNIXTime()-86400)));
	$lineobj->setFilter(array('category','equals','public',true));
	$lineobj->setLimit(20);
	$lineobj->setOrder('created','desc');
	$lines = $lineobj->get();
	fireEvent('OBStart');
	require ROOT . '/interface/common/blog/begin.php';
	require ROOT . '/interface/common/blog/line.php';
	require ROOT . '/interface/common/blog/end.php';
	fireEvent('OBEnd');
}
exit;
?>
