<?php
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => '0');
$entry = getFeedEntry($owner, $_POST['group'], $_POST['feed'], $_POST['entry'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword'], 'after', 'unread');
$result['id'] = $entry['id'];
printRespond($result);
?>