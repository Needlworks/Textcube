<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
if (strlen($suri['value'])) {
	$tag = getTagId($owner, $suri['value']);
	if ($suri['page'] === true || $suri['page'] === '1')
		$list = array('title' => $suri['value'], 'items' => getEntryListByTag($owner, $tag));
	list($entries, $paging) = getEntriesWithPagingByTag($owner, $tag, $suri['page'], $blog['entriesOnPage']);
	require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/list.php';
	require ROOT . '/lib/piece/blog/entries.php';
} else {
	$siteTags = getSiteTags($owner);
	require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/siteTags.php';
}
require ROOT . '/lib/piece/blog/end.php';
?>
