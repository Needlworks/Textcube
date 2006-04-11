<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (setBlogDescription($owner, trim($_POST['description']))) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>