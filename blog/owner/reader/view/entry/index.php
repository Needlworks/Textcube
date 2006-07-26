<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => '0');
ob_start();
printFeedEntry($owner, $_POST['group'], $_POST['feed'], $_POST['entry'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
$entry = getFeedEntry($owner, $_POST['group'], $_POST['feed'], $_POST['entry'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
$result['id'] = $entry['id'];
$result['blog'] = escapeCData($entry['blog_title']);
printRespond($result);
?>