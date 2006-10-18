<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'visibility' => array('int', 0, 3),
		'category' => array('int', 'default' => 0),
		'title' => array('string'),
		'content' => array('string'),
		'location' => array('string', 'default' => '/'),
		'tag' => array('string', 'default' => ''),
		'acceptComment' => array(array('0', '1'), 'default' => '0'),
		'acceptTrackback' => array(array('0', '1'), 'default' => '0'),
		'published' => array('int', 0, 'default' => 1)
		)
	);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
$entry['visibility'] = $_POST['visibility'];
$entry['category'] = empty($_POST['category']) ? 0 : $_POST['category'];
$entry['title'] = $_POST['title'];
if ((isset($_POST['permalink'])) && ($_POST['permalink'] != '')) {
	$entry['slogan'] = $_POST['permalink'];
}
$entry['content'] = $_POST['content'];
$entry['location'] = empty($_POST['location']) ? '/' : $_POST['location'];
$entry['tag'] = empty($_POST['tag']) ? '' : $_POST['tag'];
$entry['acceptComment'] = empty($_POST['acceptComment']) ? 0 : 1;
$entry['acceptTrackback'] = empty($_POST['acceptTrackback']) ? 0 : 1;
$entry['published'] = empty($_POST['published']) ? 1 : $_POST['published'];
if ($id = addEntry($owner, $entry)){
	fireEvent('AddPost', $id, $entry);
	setUserSetting('LatestEditedEntry',$id);
}
respondResultPage($id !== false);
?>