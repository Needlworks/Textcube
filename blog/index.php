<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '..');
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];
if (!empty($_POST['mode']) && $_POST['mode'] == 'fb') {
	$IV = array(
		'GET' => array(
			'page' => array('int', 1, 'default' => 1)
		),
		'POST' => array(
			'mode' => array(array('fb')),
			's_home_title' => array('string', 'default'=>''),
			's_name' => array('string' , 'default'=>''),
			's_no' => array('int'),
			'url' => array('string', 'default'=>''),
			's_url' => array('string', 'default'=>''),
			's_post_title' => array('string', 'default'=>''),
			'r1_no' => array('int'),
			'r1_name' => array('string', 'default'=>''),
			'r1_rno' => array('int'),
			'r1_homepage' => array('string', 'default'=>''),
			'r1_regdate' => array('timestamp'),
			'r1_body' => array('string'),
			'r1_url' => array('string', 'default'=>''),
			'r2_no' => array('int'),
			'r2_name' => array('string', 'default'=>''),
			'r2_rno' => array('int'),
			'r2_homepage' => array('string', 'default'=>''),
			'r2_regdate' => array('timestamp'),
			'r2_body' => array('string'),
			'r2_url' => array('string', 'default'=>'')
		)
	);
} else {
	$IV = array(
		'GET' => array(
			'page' => array('int', 1, 'default' => 1)
		)
	);
}
require ROOT . '/lib/includeForBlog.php';
requireModel('blog.comment');

if (false) {
	fetchConfigVal();
}
if (!empty($_POST['mode']) && $_POST['mode'] == 'fb') {
	$result = receiveNotifiedComment($_POST);
	if ($result > 0)
	    	echo '<?xml version="1.0" encoding="utf-8"?><response><error>1</error><message>error('.$result.')</message></response>';
	else
		echo '<?xml version="1.0" encoding="utf-8"?><response><error>0</error></response>';
	exit;
} else {
	$IV = array('POST' => array());
	if(!Validator::validate($IV))
		respondNotFoundPage();
	notifyComment();
}
publishEntries();
fireEvent('OBStart');
$skin = new Skin($skinSetting['skin']);
if(empty($suri['value']) && $suri["directive"] == "/" && $suri['page'] == 1 && count($metapageMappings) > 0 && getBlogSetting("metapageInitView") && isset($skin->meta)) {
	require ROOT . '/lib/piece/blog/begin.php';
	$metaView = $skin->meta;
	dress('article_rep', '', $view);
	dress('paging', '', $view);
	dress('metapage', $metapageModule, $metaView);
	dress('meta', $metaView, $view);
} else {
	list($entries, $paging) = getEntriesWithPaging($blogid, $suri['page'], $blog['entriesOnPage']);
	require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/entries.php';
}

require ROOT . '/lib/piece/blog/end.php';
fireEvent('OBEnd');
?>
