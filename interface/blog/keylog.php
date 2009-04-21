<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define('__TEXTCUBE_KEYLOG__',true);
require ROOT . '/library/includeForBlog.php';
requireComponent('Textcube.Function.Respond');

if (strlen($suri['value'])) {
	if(!$keylog = getKeylogByTitle($blogid, $suri['value'])) {
		respond::ErrorPage();
		exit;
	}
	$entries = array();
	$entries = getEntriesByKeyword($blogid, $keylog['title']);
	$skinSetting['keylogSkin'] = fireEvent('setKeylogSkin');
	if(!is_null($skinSetting['keylogSkin'])) {
		require ROOT . '/library/piece/blog/keylog.php';
	} else {
		respond::ErrorPage(_t('No handling plugin'));
	}
} else {
	$keywords = getKeywordNames($blogid, true);
	$skinSetting['keylogSkin'] = fireEvent('setKeylogSkin');
	require ROOT . '/library/piece/blog/keywords.php';
}
?>
