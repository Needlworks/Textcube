<?
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'id' => array('id')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
respondResultPage(markAsStar($owner, $_POST['id'], true));
?>