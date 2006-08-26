<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();

if ($isAjaxRequest) {
	if (deleteEntry($owner, $suri['id']) === true)
		respondResultPage(0);
	else
		respondResultPage(-1);
} else {
	deleteEntry($owner, $suri['id']);
	header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
