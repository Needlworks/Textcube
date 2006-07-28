<?
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'group' => array('int', 'min' => 0, 'mandatory' => false),
		'url' => array('url', 'max' => '255')
	)
);
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => addFeed($owner, $_POST['group'], $_POST['url']));
ob_start();
printFeeds($owner, $_POST['group']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>