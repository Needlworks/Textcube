<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (publishPostEolinSyncOnRSS($owner, $suri['id']))
	respondResultPage(0);
respondResultPage( - 1);
?>
