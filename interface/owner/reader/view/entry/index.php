<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'group' => array('int', 0),
		'feed' => array('int', 0, 'default' => 0),
		'entry' => array('int', 0, 'default' => 0),
		'unread' => array(array('0', '1')),
		'starred' => array(array('0', '1')),
		'keyword' => array('string', 'default' => '')
	)
);
require ROOT . '/library/preprocessor.php';
$result = array('error' => '0');
ob_start();
printFeedEntry($blogid, $_POST['group'], $_POST['feed'], $_POST['entry'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
$entry = getFeedEntry($blogid, $_POST['group'], $_POST['feed'], $_POST['entry'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
$result['id'] = $entry['id'];
$result['blog'] = escapeCData($entry['blog_title']);
Respond::PrintResult($result);
?>
