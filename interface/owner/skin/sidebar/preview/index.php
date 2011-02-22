<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'REQUEST' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int'),
		'safe' => array('any', 'mandatory' => false),
		'tag' => array('any', 'mandatory' => false)
		)
	);
require ROOT . '/library/preprocessor.php';
requireLibrary('blog.skin');
requireModel("blog.sidebar");
requireModel('blog.entry');
requireModel("blog.archive");
requireModel("blog.tag");
requireModel("blog.notice");
requireModel("blog.comment");
requireModel("blog.response.remote");
requireModel("blog.link");

requireStrictRoute();

$stats = Statistics::getStatistics($blogid);

function correctSidebarImage( $subject ) {
	$pattern_with_src = '/(?:\ssrc\s*=\s*["\']?)([^\s^"^>^\']+)(?:[\s">\'])/i';
	$pattern_with_background = '/(?:\sbackground\s*=\s*["\']??)([^\s^"^>^\']+)(?:[\s">\'])/i';
	$pattern_with_url_func = '/(?:url\s*\(\s*\'?)([^)]+)(?:\'?\s*\))/i';
	$return_val = preg_replace_callback($pattern_with_src , 'correctImagePath', $subject );
	$return_val = preg_replace_callback($pattern_with_background , 'correctImagePath', $return_val);
	$return_val = preg_replace_callback($pattern_with_url_func , 'correctImagePath', $return_val );
	return $return_val;
} 

function correctImagePath($match ) {
	global $skinSetting, $serviceURL;
	$pathArr = explode( "/" , $match[1]);
	if( false === $pathArr  ) 
		return $match[0];
	$c = count( $pathArr);
	if( $c <= 1 ) 
		return $match[0];
	if( $pathArr[$c-1] == "" ) 
		return $match[0];// ./s/b/c/ 이런식으로 경로만 들어있는 경우 스킵
	if( false !== array_search( "http:" , $pathArr) ) 
		return $match[0] ; // full url의 경우 스킵
	if( $pathArr[0] != '.'  && $pathArr[0] != '..' ) 
		return $match[0] ; //첫 디렉토리가 현재 디렉토리가 아닌경우 스킵
	return str_replace( $match[1],  $serviceURL . "/skin/{$skinSetting['skin']}/" . $match[1], $match[0]);
}

if (false) correctImagePath('');

function getBlogContentForSideBar()
{
	global $blogid, $blog, $blogURL, $database, $service, $stats, $skinSetting;
	
	global $pd_category, $pd_categoryXhtml, $pd_archive, $pd_calendar, $pd_tags, $pd_notices, $pd_recentEntry;
	global $pd_recentComment, $pd_recentTrackback, $pd_link, $pd_authorList;
	
	$categories = getCategories($blogid);
	$totalPosts = getEntriesTotalCount($blogid);
	
	$pd_category = getCategoriesView($totalPosts, $categories, isset($category) ? $category : true);
	$pd_categoryXhtml = getCategoriesView($totalPosts, $categories, isset($category) ? $category : true, true);
	$pd_archive = getArchives($blogid);
	$pd_calendar = getCalendarView(getCalendar($blogid, true));
	$pd_tags = getRandomTags($blogid);
	$pd_notices = getNotices($blogid);
	$pd_recentEntry = getRecentEntries($blogid);
	$pd_recentComment = getRecentComments($blogid);
	$pd_recentTrackback = getRecentTrackbacks($blogid);
	$pd_link = getLinks($blogid);
	$pd_authorList = User::getUserNamesOfBlog($blogid);
}


function pretty_dress($view)
{
	global $blogid, $blog, $blogURL, $database, $service, $stats, $skinSetting;
	
	/* local static */
	global $pd_category, $pd_categoryXhtml, $pd_archive, $pd_calendar, $pd_tags, $pd_notices, $pd_recentEntry;
	global $pd_recentComment, $pd_recentTrackback, $pd_link, $pd_authorList;
	
	if (isset($_REQUEST['safe'])) {
		// safe mode
		return '<div class="sidebar-element-safebox">&hellip;</div>';
	}
	if (isset($_REQUEST['tag'])) {
		// safe mode
		return '<div class="sidebar-element-safebox"><p>' . nl2br(htmlspecialchars($view, ENT_QUOTES)) . '</p></div>';
	}
	
	$writer = POD::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = " . User::getBlogOwner($blogid));
	$pageTitle = _t('페이지 제목');
	
	dress('page_title', htmlspecialchars($pageTitle), $view);
	dress('blogger', htmlspecialchars($writer), $view);
	dress('title', htmlspecialchars($blog['title']), $view);
	dress('desc', htmlspecialchars($blog['description']), $view);
	if (!empty($blog['logo']))
		dress('image', "{$service['path']}/attach/$blogid/{$blog['logo']}", $view);
	else
		dress('image', "{$service['path']}/resources/image/spacer.gif", $view);
	dress('blog_link', "$blogURL/", $view);
	dress('keylog_link', "$blogURL/keylog", $view);
	dress('localog_link', "$blogURL/location", $view);
	dress('taglog_link', "$blogURL/tag", $view);
	dress('guestbook_link', "$blogURL/guestbook", $view);
	
	list($view, $searchView) = Skin::cutSkinTag($view, 'search');
	dress('search_name', 'search', $searchView);
	dress('search_text', isset($search) ? htmlspecialchars($search) : '', $searchView);
	dress('search_onclick_submit', 'searchBlog()', $searchView);
	dress('search', '<form id="TTSearchForm" action="'.$blogURL.'/search/" method="get" onsubmit="return searchBlog()">'.$searchView.'</form>', $view);
	
	dress('category', $pd_category, $view);
	dress('category_list', $pd_categoryXhtml, $view);
	dress('count_total', $stats['total'], $view);
	dress('count_today', $stats['today'], $view);
	dress('count_yesterday', $stats['yesterday'], $view);
	
	list($view, $archiveView) = Skin::cutSkinTag($view, 'archive_rep');
	dress('archive_rep', getArchivesView($pd_archive, $archiveView), $view);
	dress('calendar', $pd_calendar, $view);
	list($view, $randomView) = Skin::cutSkinTag($view, 'random_tags');
	dress('random_tags', getRandomTagsView($pd_tags, $randomView), $view);
	
	list($view, $recentNoticeItem) = Skin::cutSkinTag($view, 'rct_notice_rep');	
	list($view, $noticeView) = Skin::cutSkinTag($view, 'rct_notice');
	$notices = $pd_notices;
	if (sizeof($notices) == 0) {
		$notices = array( array('title' => _t('공지 제목'), 'id' => -1));
	}
	if (sizeof($notices) > 0) {
		$itemsView = '';
		foreach ($notices as $notice) {
			$itemView = $recentNoticeItem;
			dress('notice_rep_title', htmlspecialchars(fireEvent('ViewNoticeTitle', UTF8::lessenAsEm($notice['title'], $skinSetting['recentNoticeLength']), $notice['id'])), $itemView);
			dress('notice_rep_link', "$blogURL/notice/".($blog['useSloganOnPost'] ? URL::encode($notice['slogan'], $itemView) : $notice['id']), $itemView);
			$itemsView .= $itemView;
		}
		dress('rct_notice_rep', $itemsView, $noticeView);
		dress('rct_notice', $noticeView, $view);
	}
	
	list($view, $authorList) = Skin::cutSkinTag($view, 'author_rep');
	dress('author_rep', getAuthorListView($pd_authorList, $authorList), $view);
	list($view, $recentEntry) = Skin::cutSkinTag($view, 'rctps_rep');	
	dress('rctps_rep', getRecentEntriesView($pd_recentEntry, $recentEntry), $view);
	list($view, $recentComments) = Skin::cutSkinTag($view, 'rctrp_rep');	
	dress('rctrp_rep', getRecentCommentsView($pd_recentComment, $recentComments), $view);
	list($view, $recentTrackback) = Skin::cutSkinTag($view, 'rcttb_rep');	
	dress('rcttb_rep', getRecentTrackbacksView($pd_recentTrackback, $recentTrackback), $view);
	list($view, $s_link_rep) = Skin::cutSkinTag($view, 'link_rep');	
	dress('link_rep', getLinksView($pd_link, $s_link_rep), $view);
	dress('rss_url', "$blogURL/rss", $view);
	dress('owner_url', "$blogURL/owner", $view);
	dress('textcube_name', TEXTCUBE_NAME, $view);
	dress('textcube_version', TEXTCUBE_VERSION, $view);
	
	$tagSearches = array ( '@<a @i' , '@</a *>@i', '@ id *= *".*"@isU', '@ onkey(down|up|press) *="@i', '@ on(click|load|unload) *="@i', '@<input +@i'      , '@<script.*</script *>@siU', '@<form @siU', '@</form>@siU' );
	$tagReplaces = array ( '<span ' , '</span>'  , ''                , ' onnothing="'                , ' onnothing="'                 , '<input disabled="disabled" ' , ''                        , '<div ' , '</div>');
		
	$view = preg_replace($tagSearches, $tagReplaces, $view);
	
	return correctSidebarImage($view);
}

$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);
getBlogContentForSideBar();

if (($_REQUEST['sidebarNumber'] >= 0) 	&& ($_REQUEST['sidebarNumber'] < $sidebarCount))
{
	$sidebarOrder = getSidebarModuleOrderData($sidebarCount);
	$target = $sidebarOrder[$_REQUEST['sidebarNumber']][$_REQUEST['modulePos']];
	
	$sidebarPluginArray = array();
	for ($i=0; $i<count($sidebarMappings); $i++) {
		$sidebarPluginArray[$sidebarMappings[$i]['plugin'] . '/' . $sidebarMappings[$i]['handler']]=
			array( 
				'type' => 3, 'id' => $sidebarMappings[$i]['handler'],
				'plugin' => $sidebarMappings[$i]['plugin'], 'title' =>$sidebarMappings[$i]['title'], 
				'display' => $sidebarMappings[$i]['display'],
				'identifier' => implode(':', array(3,$sidebarMappings[$i]['plugin'],$sidebarMappings[$i]['handler'])),
				'parameters' => $sidebarMappings[$i]['parameters']
				);
	}

	if ($target['type'] == 3) {
		$sidbarPluginIndex = $target['id']['plugin'] . '/' . $target['id']['handler'];
			
		if (array_key_exists($sidbarPluginIndex,  $sidebarPluginArray)) {
			$pluginURL = "{$service['path']}/plugins/{$target['id']['plugin']}";
			include_once (ROOT . "/plugins/{$target['id']['plugin']}/index.php");
			if(!empty( $configMappings[$target['id']['plugin']]['config'] ))
				$configVal = getCurrentSetting($target['id']['plugin']);
			else $configVal = '';
			echo pretty_dress(call_user_func($target['id']['handler'], $target['parameters']));
		}
	}
}
?>
