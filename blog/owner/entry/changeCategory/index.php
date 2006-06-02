<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
changeCategoryOfEntries($owner,$_POST['targets'], $_POST['category']);
respondResultPage(0);
?>