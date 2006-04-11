<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (changePassword($owner, $_POST['pwd'], $_POST['prevPwd'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>