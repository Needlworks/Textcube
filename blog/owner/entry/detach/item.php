<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
if (!empty($_GET['name']) && deleteAttachment($owner, $suri['id'], $_GET['name']))
	respondResultPage(0);
else
	respondResultPage( - 1);
?>