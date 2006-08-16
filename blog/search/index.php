<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
$search = $suri['value'];
if (!empty($search) && !empty($suri['page'])) {
	$list = array('title' => $search, 'items' => getEntryListBySearch($owner, $search));
	$commentList = getCommentList($owner, $search);
} else {  
	$list = array('title' => '', 'items' => array());  
	$commentList = array('title' => '', 'items' => array());  
}
list($entries, $paging) = getEntriesWithPagingBySearch($owner, $search, $suri['page'], $blog['entriesOnPage']);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/list.php';
require ROOT . '/lib/piece/blog/commentList.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>
