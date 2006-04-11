<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(setTotalStatistics($owner) ? 0 : - 1);
?>