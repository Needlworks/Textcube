<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (changeSetting($owner, $_POST['email'], $_POST['nickname'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>