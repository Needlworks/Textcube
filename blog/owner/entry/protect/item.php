<?
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'password' => array('string')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
respondResultPage(protectEntry($suri['id'], isset($_POST['password']) ? $_POST['password'] : ''));
?>