<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';

if (eventExists('AccessFreeSlogan')) {
	$info = fireEvent('AccessFreeSlogan', implode('/', $URLInfo['fragment']), $URLInfo);
	
	if (Validator::id($info)) {
		$entries = array();
		
		$objEntry = new Notice();
		
		if ($objEntry->doesExist($info)) {
			list($entries) = getEntryWithPaging($blogid, $info, true);
		} else {
			$objEntry = new Post();
			
			if ($objEntry->doesExist($info))
				list($entries, $paging) = getEntryWithPaging($blogid, $info);
		}
		
		fireEvent('OBStart');
		require ROOT . '/interface/common/blog/begin.php';

		if (empty($entries)) {
			header('HTTP/1.1 404 Not Found');
			if (empty($skin->pageError)) {
				dress('article_rep', '<div class="TCwarning">' . _text('존재하지 않는 페이지입니다.') . '</div>', $view);
			} else {
				dress('article_rep', NULL, $view);
				dress('page_error', $skin->pageError, $view);
			}
			unset($paging);
		} else {
			require ROOT . '/interface/common/blog/entries.php';
		}
		require ROOT . '/interface/common/blog/end.php';
		
		fireEvent('OBEnd');
		exit;
	} else if (!empty($info)) {
		echo $info;
		exit;
	}
}

requireLibrary('error');
errorExit(404);
?>
