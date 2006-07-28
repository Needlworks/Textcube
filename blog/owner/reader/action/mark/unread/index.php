<?
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'id'		=> array('id')
	)
);
require ROOT . '/lib/includeForOwner.php';
respondResultPage(markAsUnread($owner, $_POST['id']));
?>