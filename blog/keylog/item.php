<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';

if (false) {
	fetchConfigVal();
}
if (!$keyword = getKeywordByName($owner, $suri['value']))
	respondErrorPage();

$keylog = getKeylog($owner, $keyword['title']);
$skinSetting['keylogSkin'] = fireEvent('setKeylogSkin');
if($skinSetting['keylogSkin']!= null) {
	require ROOT . '/lib/piece/blog/keylog.php';
} else {
	respondErrorPage();
}
?>