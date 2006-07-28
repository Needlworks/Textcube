<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (deleteCommentNotifiedInOwner($owner, $suri['id']) === true)
	respondResultPage(0);
else
	respondResultPage( - 1);
?>