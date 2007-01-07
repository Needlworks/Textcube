<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
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
		'published' => array('int', 0, 'default' => 0)
		)
	);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
$entry['id'] = $suri['id'];
$entry['draft'] = 1;
$entry['visibility'] = $_POST['visibility'];
$entry['category'] = empty($_POST['category']) ? 0 : $_POST['category'];
$entry['title'] = $_POST['title'];
$entry['content'] = $_POST['content'];
$entry['location'] = empty($_POST['location']) ? '/' : $_POST['location'];
$entry['tag'] = empty($_POST['tag']) ? '' : $_POST['tag'];
$entry['acceptComment'] = empty($_POST['acceptComment']) ? 0 : 1;
$entry['acceptTrackback'] = empty($_POST['acceptTrackback']) ? 0 : 1;
$entry['published'] = empty($_POST['published']) ? 0 : $_POST['published'];
if (($id = saveDraftEntry($entry)) !== false){
	setUserSetting('LatestEditedEntry',$id);
	respondResultPage(0);
}
else
	respondResultPage(1);
?>