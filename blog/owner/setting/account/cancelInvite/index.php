<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = cancelInvite($_POST['userid']);
if ($result) {
	respondResultPage(0);
} else {
	respondResultPage(1);
}
?>