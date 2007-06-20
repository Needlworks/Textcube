<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'visibility' => array('int', 0, 3),
		'category' => array('int', 'default' => 0),
		'title' => array('string'),
		'content' => array('string'),
		'contentFormatter' => array('string'),
		'contentEditor' => array('string'),
		'permalink' => array('string', 'default' => ''),
		'location' => array('string', 'default' => '/'),
		'tag' => array('string', 'default' => ''),
		'acceptComment' => array(array('0', '1'), 'default' => '0'),
		'acceptTrackback' => array(array('0', '1'), 'default' => '0'),
		'published' => array('int', 0, 'default' => 1)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireModel('blog.entry');

requireStrictRoute();
if ($entry = getEntry($owner, $suri['id'])) {
	$entry['visibility'] = $_POST['visibility'];
	$entry['category'] = $_POST['category'];
	$entry['location'] = empty($_POST['location']) ? '/' : $_POST['location'];
	$entry['tag'] = empty($_POST['tag']) ? '' : $_POST['tag'];
	$entry['title'] = $_POST['title'];
	$entry['content'] = $_POST['content'];
	$entry['contentFormatter'] = $_POST['contentFormatter'];
	$entry['contentEditor'] = $_POST['contentEditor'];
	$entry['slogan'] = $_POST['permalink'];
	$entry['acceptComment'] = empty($_POST['acceptComment']) ? 0 : 1;
	$entry['acceptTrackback'] = empty($_POST['acceptTrackback']) ? 0 : 1;
	$entry['published'] = empty($_POST['published']) ? 0 : $_POST['published'];
	setUserSetting('LatestEditedEntry',$suri['id']);
	respondResultPage(updateEntry($owner, $entry));
}
respondResultPage(1);
?>
