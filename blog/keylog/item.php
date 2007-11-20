<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/includeForBlog.php';

if (false) {
	fetchConfigVal();
}
if(!$keylog = getKeylogByTitle($blogid, $suri['value'])) {
	respondErrorPage();
	exit;
}
$skinSetting['keylogSkin'] = fireEvent('setKeylogSkin');
if($skinSetting['keylogSkin']!= null) {
	require ROOT . '/lib/piece/blog/keylog.php';
} else {
	respondErrorPage();
}
?>
