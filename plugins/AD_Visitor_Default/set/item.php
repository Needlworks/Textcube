<?php
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$branchFlag = isset($_GET['javascript']) && $_GET['javascript'] == "disabled" ? true : false;

$branchFlag ? header("Location: ".$_SERVER['HTTP_REFERER']) : respondResultPage(setTotalStatistics($owner) ? 0 : - 1);
?>
