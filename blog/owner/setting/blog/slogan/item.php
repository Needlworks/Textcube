<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (useBlogSlogan($owner, $suri['id']))
	respondResultPage(0);
respondResultPage( - 1);
?>