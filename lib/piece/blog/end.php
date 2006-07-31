<?
$writer = fetchQueryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = $owner");
dress('page_title', htmlspecialchars($pageTitle), $view);
dress('blogger', htmlspecialchars($writer), $view);
dress('title', htmlspecialchars($blog['title']), $view);
dress('desc', htmlspecialchars($blog['description']), $view);
if (!empty($blog['logo']))
	dress('image', "{$service['path']}/attach/$owner/{$blog['logo']}", $view);
else
	dress('image', "{$service['path']}/image/spacer.gif", $view);
dress('blog_link', "$blogURL/", $view);
dress('keylog_link', "$blogURL/keylog", $view);
dress('localog_link', "$blogURL/location", $view);
dress('taglog_link', "$blogURL/tag", $view);
dress('guestbook_link', "$blogURL/guestbook", $view);
$searchView = $skin->search;
dress('search_name', 'search', $searchView);
dress('search_text', isset($search) ? htmlspecialchars($search) : '', $searchView);
dress('search_onclick_submit', "try{window.location.href='$blogURL/search/' + document.getElementsByName('search')[0].value.replaceAll('%', '%25'); return false;}catch(e){}", $searchView);
dress('search', $searchView, $view);
$totalPosts = getEntriesTotalCount($owner);
$categories = getCategories($owner);
dress('category', getCategoriesView($totalPosts, $categories, isset($category) ? $category : true), $view);
dress('category_list', getCategoriesView($totalPosts, $categories, isset($category) ? $category : true, true), $view);
dress('count_total', $stats['total'], $view);
dress('count_today', $stats['today'], $view);
dress('count_yesterday', $stats['yesterday'], $view);
dress('archive_rep', getArchivesView(getArchives($owner), $skin->archive), $view);
dress('calendar', getCalendarView(getCalendar($owner, isset($period) ? $period : true)), $view);
dress('random_tags', getRandomTagsView(getRandomTags($owner), $skin->randomTags), $view);
$noticeView = $skin->recentNotice;
$notices = getNotices($owner);
if (sizeof($notices) > 0) {
	$itemsView = '';
	foreach ($notices as $notice) {
		$itemView = $skin->recentNoticeItem;
		dress('notice_rep_title', htmlspecialchars(UTF8::lessenAsEm($notice['title'], $skinSetting['recentNoticeLength'])), $itemView);
		dress('notice_rep_link', "$blogURL/notice/{$notice['id']}", $itemView);
		$itemsView .= $itemView;
	}
	dress('rct_notice_rep', $itemsView, $noticeView);
	dress('rct_notice', $noticeView, $view);
}
dress('rctps_rep', getRecentEntriesView(getRecentEntries($owner), $skin->recentEntry), $view);
dress('rctrp_rep', getRecentCommentsView(getRecentComments($owner), $skin->recentComments), $view);
dress('rcttb_rep', getRecentTrackbacksView(getRecentTrackbacks($owner), $skin->recentTrackback), $view);
dress('link_rep', getLinksView(getLinks($owner), $skin->s_link_rep), $view);
dress('rss_url', "$blogURL/rss", $view);
dress('owner_url', "$blogURL/owner", $view);
if (isset($paging))
	dress('paging', getPagingView($paging, $skin->paging, $skin->pagingItem), $view);
$view = removeAllTags($view);
print $view;
?>