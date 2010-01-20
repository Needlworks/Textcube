<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'category' => array('int',0,'mandatory'=>false),
		'page' => array('int', 1, 'default' => 1)
		)
	);

require ROOT . '/library/includeForBlog.php';
if (false) {
	fetchConfigVal();
}

if(empty($suri['value'])) {
	list($entries, $paging) = getEntriesWithPaging($blogid, $suri['page'], $blog['entriesOnPage']);
} else {
	if(isset($_GET['category'])) { // category exists
		if(Validator::isInteger($_GET['category'], 0)) {
			list($entries, $paging) = getEntryWithPagingBySlogan($blogid, $suri['value'],false,$_GET['category']);
		}
	} else { // Just normal entry view
		list($entries, $paging) = getEntryWithPagingBySlogan($blogid, $suri['value']);
	}
}

fireEvent('OBStart');
require ROOT . '/library/piece/blog/begin.php';

$automaticLink = "<link rel=\"stylesheet\" href=\"{$serviceURL}/style/system.css\" type=\"text/css\" media=\"screen\" />\n";
dress('SKIN_head_end', $automaticLink."[##_SKIN_head_end_##]", $view);

if (empty($suri['value'])) {
	require ROOT . '/library/piece/blog/entries.php';
} else if (empty($entries)) {
	header('HTTP/1.1 404 Not Found');
	if (empty($skin->pageError)) { 
		dress('article_rep', '<div class="TCwarning">' . _text('존재하지 않는 페이지입니다.') . '</div>', $view);
	} else {
		dress('article_rep', NULL, $view); 
		dress('page_error', $skin->pageError, $view);
	}
	unset($paging);
} else {
	require ROOT . '/library/piece/blog/entries.php';
}



require ROOT . '/library/piece/blog/end.php';
fireEvent('OBEnd');
?>
