<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (modifyFilter($owner, $mode, $_GET['oldValue'], $_GET['newValue']) === true)
	respondResultPage(0);
else
	respondResultPage( - 1);
?>