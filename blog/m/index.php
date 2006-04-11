<?
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../..');
require ROOT . '/lib/include.php';
list($entry, $paging) = getEntriesWithPaging($owner, 1, 1);
header("Location: $blogURL/{$entry[0]['id']}");
?>