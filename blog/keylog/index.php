<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
respondNotFoundPage();
if (strlen($suri['value'])) {
	if (!$keyword = getKeywordByName($owner, $suri['value']))
		respondErrorPage();
	$keylog = getKeylog($owner, $keyword['title']);
	require ROOT . '/lib/piece/blog/keylog.php';
} else {
	$keywords = getKeywords($owner, true);
	require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/keywords.php';
	require ROOT . '/lib/piece/blog/end.php';
}
?>
