<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(setEntryVisibility($suri['id'], isset($_GET['visibility']) ? $_GET['visibility'] : 0));
?>