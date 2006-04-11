<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(deleteLink($owner, $suri['id']));
?>