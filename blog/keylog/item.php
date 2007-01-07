<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
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