<?php
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'group' => array('int', 'min' => 0, 'default' =>0),
		'feed' => array('int', 'min' => 0, 'default' => 0),
		'unread' => array(array('0','1')),
		'starred' => array(array('0','1')),
		'keyword' => array('string', 'mandatory' => false),
		'loaded' => array('int',  'mandatory' => false)
	)
);
die('aaaa')
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => '0');
ob_start();
$count = printFeedEntriesMore($owner, $_POST['group'], $_POST['feed'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword'], $_POST['loaded']);
$result['count'] = $count;
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>