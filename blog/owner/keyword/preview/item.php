<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
respondNotFoundPage();
$keyword['id'] = $suri['id'];
$keyword['title'] = $_GET['title'];
$keyword['content'] = $_GET['content'];
$keylog = getKeylog($owner, $keyword['title']);
require ROOT . '/lib/piece/blog/keylog.php';
?>