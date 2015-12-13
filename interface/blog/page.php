<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';

define('__TEXTCUBE_PAGE__',true);
if (isset($suri['id']) || (isset($suri['value']) && strlen($suri['value']) > 0)) {
	if (!isset($suri['id']) || (Setting::getBlogSettingGlobal('useSloganOnPost',1) == 1)) {
		list($entries, $paging) = getEntryWithPagingBySlogan($blogid, $suri['value'], 'page');
	} else {
		list($entries, $paging) = getEntryWithPaging($blogid, $suri['id'], 'page');
	}
	if(count($entries) === 1) unset($paging);
	fireEvent('OBStart');
	require ROOT . '/interface/common/blog/begin.php';
	
	if (empty($entries)) {
		header('HTTP/1.1 404 Not Found');
		if (empty($skin->pageError)) {
			dress('article_rep', fireEvent('ViewErrorPage','<div class="TCwarning">' . _text('존재하지 않는 페이지입니다.') . '</div>'), $view);
		} else {
			dress('article_rep', NULL, $view);
			dress('page_error', fireEvent('ViewErrorPage',$skin->pageError), $view);
		}
		unset($paging);
	} else {
		require ROOT . '/interface/common/blog/entries.php';
	}
	require ROOT . '/interface/common/blog/end.php';
	fireEvent('OBEnd');
} else {
	if(!empty($freeSlogan)) {
		errorExit(404);
	}
	list($entries, $paging) = getEntriesWithPagingByPage($blogid, $suri['page'], $blog['entriesOnPage']);
	fireEvent('OBStart');
	require ROOT . '/interface/common/blog/begin.php';
	require ROOT . '/interface/common/blog/entries.php';
	require ROOT . '/interface/common/blog/end.php';
	fireEvent('OBEnd');
}
?>
