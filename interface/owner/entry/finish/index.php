<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'visibility' => array('int', 0, 3),
		'starred'    => array('int', 0, 2),
		'category'   => array('int', 'default' => 0),
		'title'      => array('string'),
		'content'    => array('string'),
		'contentformatter' => array('string'),
		'contenteditor'    => array('string'),
		'permalink'  => array('string', 'default' => ''),
		'location'   => array('string', 'default' => '/'),
		'latitude'   => array('number', 'default' => null, 'min' => -90.0, 'max' => 90.0, 'bypass' => true),
		'longitude'   => array('number', 'default' => null, 'min' => -180.0, 'max' => 180.0, 'bypass' => true),
		'tag'        => array('string', 'default' => ''),
		'acceptcomment'    => array(array('0', '1'), 'default' => '0'),
		'accepttrackback'  => array(array('0', '1'), 'default' => '0'),
		'published'  => array('int', 0, 'default' => 1)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel('blog.entry');

requireStrictRoute();

if(empty($suri['id'])) {
	$entry = array();
} else {
	$updateDraft = 0;
	$entry = getEntry($blogid, $suri['id']);
	if(is_null($entry)) {
		$entry = getEntry($blogid, $suri['id'],true);
		$updateDraft = 1;
	}
}
if (empty($suri['id']) || !is_null($entry)) {
	$entry['visibility'] = $_POST['visibility'];
	$entry['starred']    = $_POST['starred'];
	$entry['category']   = $_POST['category'];
	$entry['location']   = empty($_POST['location']) ? '/' : $_POST['location'];
	$entry['latitude'] = empty($_POST['latitude']) ? null : $_POST['latitude'];
	$entry['longitude'] = empty($_POST['longitude']) ? null : $_POST['longitude'];
	$entry['tag']        = empty($_POST['tag']) ? '' : $_POST['tag'];
	$entry['title']      = $_POST['title'];
	$entry['content']    = $_POST['content'];
	$entry['contentformatter'] = $_POST['contentformatter'];
	$entry['contenteditor']    = $_POST['contenteditor'];
	if ((isset($_POST['permalink'])) && ($_POST['permalink'] != '')) {
		$entry['slogan'] = $_POST['permalink'];
	} else if($_POST['permalink'] == '') $entry['slogan'] = '';
	$entry['acceptcomment'] = empty($_POST['acceptcomment']) ? 0 : 1;
	$entry['accepttrackback'] = empty($_POST['accepttrackback']) ? 0 : 1;
	$entry['published'] = empty($_POST['published']) ? 0 : $_POST['published'];
	$entry['draft'] = 0;
	if(strpos($entry['slogan'],'TCDraftPost') === 0) $entry['slogan'] = $entry['title'];

	if(empty($suri['id'])) {
		if ($id = addEntry($blogid, $entry)) {
			fireEvent('AddPost', $id, $entry);
			setBlogSetting('LatestEditedEntry_user'.getUserId(),$id);
			$result = array();
			$result['error'] = (($id !== false) === true ? 0 : 1);
			$result['entryId'] = $id;
			Respond::PrintResult($result);
			exit;
		}
	} else {
		if($id = updateEntry($blogid, $entry, $updateDraft)) {
			fireEvent('UpdatePost', $id, $entry);
			setBlogSetting('LatestEditedEntry_user'.getUserId(),$suri['id']);
			Respond::ResultPage(0);
			exit;
		}
	}
}
Respond::ResultPage(-1);
?>
