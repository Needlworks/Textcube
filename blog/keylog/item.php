<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';

if (false) {
	fetchConfigVal();
}
if (!$keyword = getKeywordByName($owner, $suri['value']))
	respondErrorPage();
$keylog = getKeylog($owner, $keyword['title']);

require ROOT . '/lib/piece/blog/keylog.php';
?>
