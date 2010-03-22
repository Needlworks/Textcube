<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

//handleTags($view);

$pageTitle = trim($pageTitle);
if (!empty($pageTitle)) {
	$pageTitleView = $skin->pageTitle;
	if(!empty($pageTitleView)) {
		dress('page_post_title', htmlspecialchars($pageTitle), $pageTitleView);
		dress('page_title', $pageTitleView, $view);
	} else {
		// Legacy. (for 1.0/1.1 skins)
		dress('page_title', htmlspecialchars($pageTitle), $view);
	}
}

if(isset($totalTags)) {
	$totalTags = array_unique($totalTags);
	$totalTagsView = implode(",",$totalTags);
} else {
	$totalTagsView = getBlogTags($blogid);
}

dress('meta_http_equiv_keywords', $totalTagsView, $view);

$searchView = $skin->search;
dress('search_name', 'search', $searchView);
dress('search_text', isset($search) ? htmlspecialchars($search) : '', $searchView);
dress('search_onclick_submit', 'searchBlog()', $searchView);
dress('search', '<form id="TTSearchForm" action="'.$blogURL.'/search/" method="get" onsubmit="return searchBlog()">'.$searchView.'</form>', $view);

$totalPosts = getEntriesTotalCount($blogid);
$categories = getCategories($blogid);

if (preg_match("@\\[##_category_##\\]@iU", $view))
	dress('category', getCategoriesView($totalPosts, $categories, isset($category) ? $category : true), $view, false, true);
if (preg_match("@\\[##_category_list_##\\]@iU", $view))
	dress('category_list', getCategoriesView($totalPosts, $categories, isset($category) ? $category : true, true), $view, false, true);
dress('count_total', $stats['total'], $view);
dress('count_today', $stats['today'], $view);
dress('count_yesterday', $stats['yesterday'], $view);
if (preg_match("@\\[##_archive_rep_##\\]@iU", $view))
	dress('archive_rep', getArchivesView(getArchives($blogid), $skin->archive), $view, false, true);
if (preg_match("@\\[##_calendar_##\\]@iU", $view))
	dress('calendar', getCalendarView(getCalendar($blogid, isset($period) ? $period : true)), $view, false, true);
if (preg_match("@\\[##_random_tags_##\\]@iU", $view))
	dress('random_tags', getRandomTagsView(getRandomTags($blogid), $skin->randomTags), $view, false, true);

if (preg_match("@\\[##_rct_notice_##\\]@iU", $view)) {
	$noticeView = getRecentNoticesView(getRecentNotices($blogid), $skin->recentNotice, $skin->recentNoticeItem, false);
	dress('rct_notice', $noticeView, $view, false, true);
}
if (preg_match("@\\[##_rct_page_##\\]@iU", $view)) {
	$noticeView = getRecentNoticesView(getNotices($blogid), $skin->recentPage, $skin->recentPageItem, true);
	dress('rct_page', $noticeView, $view, false, true);
}
if (preg_match("@\\[##_author_rep_##\\]@iU", $view))
	dress('author_rep', getAuthorListView(User::getUserNamesOfBlog($blogid), $skin->authorList), $view, false, true);

// Recent items
if (preg_match("@\\[##_rctps_##\\]@iU", $view)) {
	dress('rctps', getRecentEntriesView(getRecentEntries($blogid), $skin->recentEntry, $skin->recentEntryItem), $view, false, true);
} else if (preg_match("@\\[##_rctps_rep_##\\]@iU", $view)) {
	dress('rctps_rep', getRecentEntriesView(getRecentEntries($blogid), null, $skin->recentEntryItem), $view, false, true);
}
if (preg_match("@\\[##_rctrp_##\\]@iU", $view)) {
	dress('rctrp', getRecentCommentsView(getRecentComments($blogid), $skin->recentComment, $skin->recentCommentItem), $view, false, true);
} else if (preg_match("@\\[##_rctrp_rep_##\\]@iU", $view)) {
	dress('rctrp_rep', getRecentCommentsView(getRecentComments($blogid), null, $skin->recentCommentItem), $view, false, true);
}
if (preg_match("@\\[##_rcttb_##\\]@iU", $view)) {
	dress('rcttb', getRecentTrackbacksView(getRecentTrackbacks($blogid), $skin->recentTrackback, $skin->recentTrackbackItem), $view, false, true);
} else if (preg_match("@\\[##_rcttb_rep_##\\]@iU", $view)) {
	dress('rcttb_rep', getRecentTrackbacksView(getRecentTrackbacks($blogid), null, $skin->recentTrackbackItem), $view, false, true);
}
// ETC
if (preg_match("@\\[##_link_rep_##\\]@iU", $view))
	dress('link_rep', getLinksView(getLinks($blogid), $skin->link_rep), $view, false, true);
if (preg_match("@\\[##_link_list_##\\]@iU", $view))
	dress('link_list', getLinkListView(getLinks($blogid)), $view, false, true);

if (preg_match("@\\[##_paging_##\\]@iU", $view)) {
	if (isset($paging)) {
		if(isset($cache) && strpos($cache->name,'Paging')!==false) {
			if($cache->load()) {
				$pagingView = $cache->contents;
			} else {
				$pagingView = getPagingView($paging, $skin->paging, $skin->pagingItem);
				$cache->contents = $pagingView;
				$cache->update();
			}
		} else {
			$pagingView = getPagingView($paging, $skin->paging, $skin->pagingItem);
		}
		dress('paging_list', $pagingView, $view, false, true);
		if(!empty($entries) || $suri['directive'] == '/guestbook') dress('paging', $pagingView, $view, false, true);
		$url = URL::encode($paging['url'],$service['useEncodedURL']);
		$prefix = $paging['prefix'];
		$postfix = isset($paging['postfix']) ? $paging['postfix'] : '';
		// prev_page, next_page for legacy support.
		dress('prev_page', isset($paging['prev']) ? "href='$url$prefix{$paging['prev']}$postfix'" : '',$view);
		dress('prev_page_title', isset($paging['prev_title']) ? $paging['prev_title'] : '',$view);
		dress('next_page', isset($paging['next']) ? "href='$url$prefix{$paging['next']}$postfix'" : '',$view);
		dress('next_page_title', isset($paging['next_title']) ? $paging['next_title'] : '',$view);
	} else if(isset($cache) && strpos($cache->name,'Paging')!==false && $cache->load()) {
		dress('paging', $cache->contents, $view, false, true);
	}
}

// Sidebar dressing
$sidebarElements = array_keys($skin->sidebarStorage);
if(!empty($sidebarElements)) {
	foreach ($sidebarElements as $element) {
		$pluginData = $skin->sidebarStorage[$element];
		$plugin = $pluginData['plugin'];
		include_once (ROOT . "/plugins/{$plugin}/index.php");
		$pluginURL = "{$service['path']}/plugins/{$plugin}";
		$pluginPath = ROOT . "/plugins/{$plugin}";
		if( !empty( $configMappings[$plugin]['config'] ) ) 				
			$configVal = getCurrentSetting($plugin);
		else
			$configVal ='';

		dress($element, call_user_func($pluginData['handler'], $pluginData['parameters']), $view);
	}
}

// Coverpage dressing
$coverpageElements = array_keys($skin->coverpageStorage);
foreach ($coverpageElements as $element) {
	dress($element, $skin->coverpageStorage[$element], $view);
}
$view = revertTempTags(removeAllTags($view));
print $view;
$gCacheStorage->save();
?>
