<?php
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => editFeed($owner, $_POST['id'], $_POST['old_group'], $_POST['new_group'], $_POST['url']));
ob_start();
printFeeds($owner, $_POST['old_group']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>