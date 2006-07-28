<?
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'id' => array('id'),
		'group' => array('int')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
$result = array('error' => deleteFeed($owner, $_POST['id']));
ob_start();
printFeeds($owner, $_POST['group']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>