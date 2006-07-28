<?php
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'group' => array('int', 'min' => 0),
		'feed' => array('int', 'min' => 0),
		'unread' => array(array('0','1') ),
		'starred' => array(array('0','1') ),
		'keyword'  => array('any', 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => '0');
ob_start();
$count = printFeedEntries($owner, $_POST['group'], $_POST['feed'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
$entry = getFeedEntry($owner, $_POST['group'], $_POST['feed'], 0, $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
$result['firstEntryId'] = $entry['id'];
$result['entriesShown'] = $count;
$result['entriesTotal'] = getFeedEntriesTotalCount($owner, $_POST['group'], $_POST['feed'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
printRespond($result);
?>