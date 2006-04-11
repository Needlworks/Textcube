<?
define('ROOT', '../..');
require ROOT . '/lib/include.php';
list($entries, $paging) = getEntryWithPaging($owner, $suri['id'], true);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>