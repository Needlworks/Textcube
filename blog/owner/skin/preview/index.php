<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
list($entries, $paging) = getEntriesWithPaging($owner, $suri['page'], $blog['entriesOnPage']);
$skinSetting['skin'] = $_GET['skin'];
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>
