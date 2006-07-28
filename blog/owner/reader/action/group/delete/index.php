<?
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'id' => array('id'),
		'current' => array('int', 0)
	)
);
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => deleteFeedGroup($owner, $_POST['id']));
ob_start();
printFeedGroups($owner, $_POST['current']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>