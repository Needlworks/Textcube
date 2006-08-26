<?php
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();
$isAjaxRequest ? respondResultPage(setTotalStatistics($owner) ? 0 : -1) : header("Location: ".$_SERVER['HTTP_REFERER']);
?>
