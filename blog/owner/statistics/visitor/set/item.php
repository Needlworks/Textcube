<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';

$branchFlag = isset($_GET['javascript']) && $_GET['javascript'] == "disabled" ? true : false;

$branchFlag ? header("Location: ".$_SERVER['HTTP_REFERER']) : respondResultPage(setTotalStatistics($owner) ? 0 : - 1);
?>