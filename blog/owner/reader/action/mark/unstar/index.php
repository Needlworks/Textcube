<?
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(markAsStar($owner, $_POST['id'], false));
?>