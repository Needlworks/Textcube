<?
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (deleteTrackbackLog($owner, $suri['id']) !== false)
	respondResultPage(0);
else
	respondResultPage( - 1);
?> 