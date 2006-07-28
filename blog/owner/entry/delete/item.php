<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (deleteEntry($owner, $suri['id']) === true) {
	respondResultPage(0);
} else
	respondResultPage( - 1);
?>