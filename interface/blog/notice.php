<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}

if(isset($suri['id'])) {
	list($entries, $paging) = getEntryWithPaging($blogid, $suri['id'], true);
	fireEvent('OBStart');
	require ROOT . '/lib/piece/blog/begin.php';
	if (empty($entries)) {
		header('HTTP/1.1 404 Not Found');
		if (empty($skin->pageError)) {
			dress('article_rep', '<div class="TCwarning" style="background-image:url(\'' . $service['path'] . '/image/warning.gif\');">' . _text('존재하지 않는 페이지입니다.') . '</div>', $view);
		} else {
			dress('article_rep', NULL, $view);
			dress('page_error', $skin->pageError, $view);
		}
		unset($paging);
	} else {
		require ROOT . '/lib/piece/blog/entries.php';
	}
	require ROOT . '/lib/piece/blog/end.php';
	fireEvent('OBEnd');
} else {
	list($entries, $paging) = getEntriesWithPagingByNotice($blogid, $suri['page'], $blog['entriesOnPage']);
	fireEvent('OBStart');
	require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/entries.php';
	require ROOT . '/lib/piece/blog/end.php';
	fireEvent('OBEnd');
}
?>
