<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'group' => array('int', 'min' => 0, 'default' => 0),
		'feed' => array('int', 'min' => 0, 'default' => 0),
		'unread' => array(array('0','1') ),
		'starred' => array(array('0','1') ),
		'keyword'  => array('string', 'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
$result = array('error' => '0');
ob_start();
$count = printFeedEntries($blogid, $_POST['group'], $_POST['feed'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
$entry = getFeedEntry($blogid, $_POST['group'], $_POST['feed'], 0, $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
$result['firstEntryId'] = $entry['id'];
$result['entriesShown'] = $count;
$result['entriesTotal'] = getFeedEntriesTotalCount($blogid, $_POST['group'], $_POST['feed'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
Respond::PrintResult($result);
?>
