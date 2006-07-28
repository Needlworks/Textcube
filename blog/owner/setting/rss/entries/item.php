<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (setEntriesOnRSS($owner, $suri['id'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>