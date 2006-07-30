<?php
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'group' => array('int'),
		'url' => array('url')
	) 
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
$result = array('error' => addFeed($owner, $_POST['group'], $_POST['url']));
ob_start();
printFeeds($owner, $_POST['group']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>