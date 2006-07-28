<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
respondResultPage(setTotalStatistics($owner) ? 0 : - 1);
?>