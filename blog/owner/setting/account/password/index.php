<?
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'pwd' => array('string'),
		'prevPwd' => array('string')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (changePassword($owner, $_POST['pwd'], $_POST['prevPwd'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>