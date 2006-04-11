<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(!empty($_GET['url']) && sendTrackback($owner, $suri['id'], $_GET['url']));
?>