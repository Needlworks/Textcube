<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$writer = User::getBlogOwnerName($blogid);
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
dress('title', htmlspecialchars($blog['title']), $view);
dress('blogger', htmlspecialchars($writer), $view);
dress('desc', htmlspecialchars($blog['description']), $view);
if (!empty($blog['logo']))
	dress('image', "{$service['path']}/attach/$blogid/{$blog['logo']}", $view);
else
	dress('image', "{$service['path']}/image/spacer.gif", $view);
dress('blog_link', "$blogURL/", $view);
dress('keylog_link', "$blogURL/keylog", $view);
dress('localog_link', "$blogURL/location", $view);
dress('taglog_link', "$blogURL/tag", $view);
dress('guestbook_link', "$blogURL/guestbook", $view);

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
dress('search', '<form id="TTSearchForm" action="'.$blogURL.'/search/" method="get" onsubmit="return searchBlog()" style="margin:0;padding:0;display:inline">'.$searchView.'</form>', $view);

$totalPosts = getEntriesTotalCount($blogid);
$categories = getCategories($blogid);
dress('category', getCategoriesView($totalPosts, $categories, isset($category) ? $category : true), $view);
dress('category_list', getCategoriesView($totalPosts, $categories, isset($category) ? $category : true, true), $view);
dress('count_total', $stats['total'], $view);
dress('count_today', $stats['today'], $view);
dress('count_yesterday', $stats['yesterday'], $view);
dress('archive_rep', getArchivesView(getArchives($blogid), $skin->archive), $view);
dress('calendar', getCalendarView(getCalendar($blogid, isset($period) ? $period : true)), $view);
dress('random_tags', getRandomTagsView(getRandomTags($blogid), $skin->randomTags), $view);
$noticeView = $skin->recentNotice;
$notices = getNotices($blogid);
if (sizeof($notices) > 0) {
	$itemsView = '';
	foreach ($notices as $notice) {
		$itemView = $skin->recentNoticeItem;
		dress('notice_rep_title', htmlspecialchars(fireEvent('ViewNoticeTitle', UTF8::lessenAsEm($notice['title'], $skinSetting['recentNoticeLength']), $notice['id'])), $itemView);
		dress('notice_rep_link', "$blogURL/notice/{$notice['id']}", $itemView);
		$itemsView .= $itemView;
	}
	dress('rct_notice_rep', $itemsView, $noticeView);
	dress('rct_notice', $noticeView, $view);
}
dress('author_rep', getAuthorListView(getUserNamesOfBlog($blogid), $skin->authorList), $view);
dress('rctps_rep', getRecentEntriesView(getRecentEntries($blogid), $skin->recentEntry), $view);
dress('rctrp_rep', getRecentCommentsView(getRecentComments($blogid), $skin->recentComments), $view);
dress('rcttb_rep', getRecentTrackbacksView(getRecentTrackbacks($blogid), $skin->recentTrackback), $view);
$links = getLinks( $blogid );
dress('link_rep', getLinksView($links, $skin->s_link_rep), $view);
dress('rss_url', "$blogURL/rss", $view);
dress('comment_rss_url', "$blogURL/comment/rss", $view);
dress('owner_url', "$blogURL/owner", $view);
dress('textcube_name', TEXTCUBE_NAME, $view);
dress('textcube_version', TEXTCUBE_VERSION, $view);
dress('tattertools_name', TEXTCUBE_NAME, $view); // For skin legacy.
dress('tattertools_version', TEXTCUBE_VERSION, $view);

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
	dress('paging', $pagingView, $view);
	$url = URL::encode($paging['url'],$service['useEncodedURL']);
	$prefix = $paging['prefix'];
	$postfix = isset($paging['postfix']) ? $paging['postfix'] : '';
	dress('prev_page', isset($paging['prev']) ? "href='$url$prefix{$paging['prev']}$postfix'" : '',$view);
	dress('next_page', isset($paging['next']) ? "href='$url$prefix{$paging['next']}$postfix'" : '',$view);
} else if(isset($cache) && strpos($cache->name,'Paging')!==false && $cache->load()) {
	dress('paging', $cache->contents, $view);
}

$sidebarElements = array_keys($skin->sidebarStorage);
foreach ($sidebarElements as $element) {
	dress($element, $skin->sidebarStorage[$element], $view);
}
$coverpageElements = array_keys($skin->coverpageStorage);
foreach ($coverpageElements as $element) {
	dress($element, $skin->coverpageStorage[$element], $view);
}
$view = revertTempTags(removeAllTags($view));

$links = getLinks( $blogid );
foreach ($links as $link) {
	if( !$link['visibility'] || !$link['xfn'] ) {
		continue;
	}
	addXfnAttrs( $link['url'], $link['xfn'], $view );
}

print $view;

?>
