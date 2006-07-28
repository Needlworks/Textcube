<?
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'write' => array(array('0', '1')),
		'comment' => array(array('0', '1'))
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (setGuestbook($owner, $_GET['write'], $_GET['comment'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>