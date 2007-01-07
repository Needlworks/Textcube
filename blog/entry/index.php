<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
list($entries, $paging) = getEntryWithPagingBySlogan($owner, $suri['value']);
require ROOT . '/lib/piece/blog/begin.php';
if (empty($entries)) {
	header('HTTP/1.1 404 Not Found');
	dress('article_rep', '<div style="text-align:center;font-size:14px;font-weight:bold;padding-top:50px;margin:50px 0;color:#333;background:url(' . $service['path'] . '/image/warning.gif) no-repeat top center;">' . _text('존재하지 않는 페이지입니다.') . '</div>', $view);
} else {
	require ROOT . '/lib/piece/blog/entries.php';
}
require ROOT . '/lib/piece/blog/end.php';
?>
