<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (!empty($_GET['name']) && deleteAttachment($owner, $suri['id'], $_GET['name']))
	respondResultPage(0);
else
	respondResultPage(-1);
?>