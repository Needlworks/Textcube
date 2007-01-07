<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
list($entry, $paging) = getEntriesWithPaging($owner, 1, 1);
if(empty($entry))
	printMobileErrorPage(_text('페이지 오류'), _text('글이 하나도 없습니다.'), $blogURL);
else
	header("Location: $blogURL/{$entry[0]['id']}");
?>
