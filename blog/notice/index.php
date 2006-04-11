<?
define('ROOT', '../..');
require ROOT . '/lib/include.php';
list($entries, $paging) = getEntriesWithPagingByNotice($owner, $suri['page'], $blog['entriesOnPage']);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>