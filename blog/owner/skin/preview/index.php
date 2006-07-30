<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
list($entries, $paging) = getEntriesWithPaging($owner, $suri['page'], $blog['entriesOnPage']);
if(!Validator::filename($_GET['skin']) && $_GET['skin'] != "customize/$owner")
	respondNotFoundPage();
$skinSetting['skin'] = $_GET['skin'];
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>
