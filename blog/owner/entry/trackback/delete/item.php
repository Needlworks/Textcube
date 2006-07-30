<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (trashTrackback($owner, $suri['id']) !== true)
	respondResultPage(0);
else
	$branchFlag ? header("Location: ".$_SERVER['HTTP_REFERER']) : respondResultPage( - 1);
?>
