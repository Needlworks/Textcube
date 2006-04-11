<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (deleteCommentNotifiedInOwner($owner, $suri['id']) === true)
	respondResultPage(0);
else
	respondResultPage( - 1);
?>