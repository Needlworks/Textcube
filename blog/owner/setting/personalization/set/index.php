<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(setPersonalization($owner, $_POST['column'], $_POST['value']));
?>