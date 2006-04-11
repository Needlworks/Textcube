<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (setEntriesOnRSS($owner, $suri['id'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>