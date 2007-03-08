<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/includeForBlog.php';

if (false) {
	fetchConfigVal();
}
if (strlen($suri['value'])) {
	if (!$keyword = getKeywordByName($owner, $suri['value']))
		respondErrorPage();
	$keylog = getKeylog($owner, $keyword['title']);
	$skinSetting['keylogSkin'] = fireEvent('setKeylogSkin');
	if($skinSetting['keylogSkin']!= null) {
		require ROOT . '/lib/piece/blog/keylog.php';
	} else {
		respondErrorPage(_t('No handling plugin'));
	}
} else {
	$keywords = getKeywords($owner, true);
	$skinSetting['keylogSkin'] = fireEvent('setKeylogSkin');
	if($skinSetting['keylogSkin']!= null) {
		require ROOT . '/lib/piece/blog/begin.php';
		require ROOT . '/lib/piece/blog/keywords.php';
		require ROOT . '/lib/piece/blog/end.php';
	} else {
		respondErrorPage(_t('No handling plugin'));
	}
}
?>