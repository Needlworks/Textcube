<?php
define('ROOT', '../../../../../..');

require ROOT . '/lib/includeForOwner.php';

$isAjaxRequest = checkAjaxRequest();

if (deleteCommentInOwner($owner, $suri['id']) === true)
	$isAjaxRequest ? respondResultPage(0) : header("Location: ".$_SERVER['HTTP_REFERER']);
else
	$isAjaxRequest ? respondResultPage(-1) : header("Location: ".$_SERVER['HTTP_REFERER']);
?>
