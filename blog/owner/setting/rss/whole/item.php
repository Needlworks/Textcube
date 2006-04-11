<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (setPublishWholeOnRSS($owner, $suri['id'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>