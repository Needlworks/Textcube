<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'visibility' => array('int', 0, 3),
		'starred' => array('int', 0, 2),
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
		'published' => array('int', 0, 'default' => 1),
		'draft' => array(array('0', '1'), 'default' => '0')
		)
	);
requireModel("blog.entry");


requireStrictRoute();
$entry = array();
$entry['visibility'] = $_POST['visibility'];
$entry['starred'] = $_POST['starred'];
$entry['category'] = empty($_POST['category']) ? 0 : $_POST['category'];
$entry['title'] = $_POST['title'];
if ((isset($_POST['permalink'])) && ($_POST['permalink'] != '')) {
	$entry['slogan'] = $_POST['permalink'];
}
$entry['content'] = $_POST['content'];
$entry['contentFormatter'] = $_POST['contentFormatter'];
$entry['contentEditor'] = $_POST['contentEditor'];
$entry['location'] = empty($_POST['location']) ? '/' : $_POST['location'];
$entry['tag'] = empty($_POST['tag']) ? '' : $_POST['tag'];
$entry['acceptComment'] = empty($_POST['acceptComment']) ? 0 : 1;
$entry['acceptTrackback'] = empty($_POST['acceptTrackback']) ? 0 : 1;
$entry['published'] = empty($_POST['published']) ? 1 : $_POST['published'];
$entry['draft'] = empty($_POST['draft']) ? 0 : $_POST['draft'];
if ($id = addEntry($blogid, $entry)) {
	fireEvent('AddPost', $id, $entry);
	setBlogSetting('LatestEditedEntry_user'.getUserId(),$id);
}
$result = array();
$result['error'] = (($id !== false) === true ? 0 : 1);
$result['entryId'] = $id;
Respond::PrintResult($result);
?>
