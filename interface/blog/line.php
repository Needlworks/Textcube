<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'key' => array('string','default'=>''),
		'content' => array('string','default'=>''),
		'page' => array('int',1,'default'=>'')
	),
	'POST' => array(
		'key' => array('string','default'=>''),
		'content' => array('string','default'=>'')
	)
);

require ROOT . '/library/preprocessor.php';

if(!empty($_POST['key']) && !empty($_POST['content'])) {
	$key = $_POST['key'];
	$content = $_POST['content'];
} else {
	$key = $_GET['key'];
	$content = $_GET['content'];
}	

$lineobj = Line::getInstance();
$lineobj->reset();
// If line comes.
if(!empty($key) && !empty($content)) {
	$password = Setting::getBlogSetting('LinePassword', null, true);
	if($password == $key) {
		$lineobj->content = $content;
		$result = $lineobj->add();
		$cache = new pageCache;
		$cache->name = 'linesATOM';
		$cache->purge();
		$cache->reset();
		$cache->name = 'linesRSS';
		$cache->purge();
		$lineobj->showResult($result);
	}
} else {
	/// Prints public lines
	$lineobj->setFilter(array('created','bigger',(Timestamp::getUNIXTime()-86400)));
	$lineobj->setFilter(array('category','equals','public',true));

	$lines = $lineobj->get();

	fireEvent('OBStart');
	require ROOT . '/interface/common/blog/begin.php';
	require ROOT . '/interface/common/blog/line.php';
	require ROOT . '/interface/common/blog/end.php';
	fireEvent('OBEnd');
}
exit;
?>
