<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (deleteCommentInOwner($owner, $suri['id']) === true)
	respondResultPage(0);
else
	respondResultPage( - 1);
?>