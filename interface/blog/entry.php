<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
		'GET' => array(
			'category' => array('int',0,'mandatory'=>false),
			'page' => array('int', 1, 'default' => 1),
			'commentId' => array('int',0,'mandatory'=>false),
			'commentInput' => array('bool','mandatory'=>false)
			)
		);

require ROOT . '/library/preprocessor.php';

if(Setting::getBlogSettingGlobal('useiPhoneUI',true) && (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'],'iPod') || strpos($_SERVER['HTTP_USER_AGENT'],'iPhone')))){
	if(empty($suri['value'])) {
		header("Location: $blogURL/i"); exit;
	} else {
		header("Location: $blogURL/i/entry/".$suri['value']); exit;
	}
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
		if(isset($_GET['commentId']) || isset($_GET['commentInput'])) {
			if(isset($_GET['commentId']) && Validator::isInteger($_GET['commentId'],1)) {
				$commentId = $_GET['commentId'];
			} else {
				$commentId = 1;
			}
			$suri['page'] = getCommentPageById(getBlogId(),$entries[0]['id'],$commentId);
			$context->setProperty('blog.showCommentBox',true);
		}
	}
}

fireEvent('OBStart');
require ROOT . '/interface/common/blog/begin.php';

$automaticLink = "<link rel=\"stylesheet\" href=\"{$serviceURL}/resources/style/system.css\" type=\"text/css\" media=\"screen\" />\n";
dress('SKIN_head_end', $automaticLink."[##_SKIN_head_end_##]", $view);

if (empty($suri['value'])) {
	require ROOT . '/interface/common/blog/entries.php';
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
	require ROOT . '/interface/common/blog/entries.php';
}

require ROOT . '/interface/common/blog/end.php';
fireEvent('OBEnd');
?>
