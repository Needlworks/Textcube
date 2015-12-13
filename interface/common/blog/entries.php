<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$entriesView = '';
importlib('model.blog.comment');
$context = Model_Context::getInstance();
if (isset($cache->contents)) {
	$entriesView = $cache->contents;
	if(strpos($cache->name,'keyword')!==false) $isKeylog = true;
} else if(isset($entries)) {
	$totalTags = array();
	$entryRsses = '';
	foreach ($entries as $entry) {
//		$entryRsses .= '<link rel="alternate" type="application/rss+xml" '.
//			'title="Trackback: '.htmlspecialchars($entry['title']).' - '.htmlspecialchars($context->getProperty('blog.title')).'" '.
//			'href="'.$context->getProperty('uri.default').'/rss/trackback/'.$entry['id'].'" />'.CRLF;
//		$entryRsses .= '<link rel="alternate" type="application/rss+xml" '.
//			'title="Comment: '.htmlspecialchars($entry['title']).' - '.htmlspecialchars($context->getProperty('blog.title')).'" '.
//			'href="'.$context->getProperty('uri.default').'/rss/comment/'.$entry['id'].'" />'.CRLF;
		$entryRsses .= '	<link rel="alternate" type="application/rss+xml" '.
			'title="Responses (RSS) : '.htmlspecialchars($entry['title']).' - '.htmlspecialchars($context->getProperty('blog.title')).'" '.
			'href="'.$context->getProperty('uri.default').'/rss/response/'.$entry['id'].'" />'.CRLF.
			'	<link rel="alternate" type="application/atom+xml" '.
			'title="Responses (ATOM) : '.htmlspecialchars($entry['title']).' - '.htmlspecialchars($context->getProperty('blog.title')).'" '.
			'href="'.$context->getProperty('uri.default').'/atom/response/'.$entry['id'].'" />'.CRLF;
	}
	if( Setting::getBlogSettingGlobal('useFOAF',1) && rtrim( $suri['url'], '/' ) == $context->getProperty('uri.path') ) {
		/* same code exists in cover.php */
		$foafDiscovery = "<link rel=\"meta\" type=\"application/rdf+xml\" title=\"FOAF\" href=\"".$context->getProperty('uri.default')."/foaf\" />\n";
	} else {
		$foafDiscovery = "";
	}
	dress('SKIN_head_end', $foafDiscovery.$entryRsses."[##_SKIN_head_end_##]", $view);
	dress('foaf_url', $context->getProperty('uri.default')."/foaf", $view);
	
	foreach ($entries as $entry) {
		if ($suri['directive'] == '/notice')
			$permalink = $context->getProperty('uri.blog')."/notice/" . ($blog['useSloganOnPost'] ? URL::encode($entry['slogan'], $service['useEncodedURL']) : $entry['id']);
		else if ($suri['directive'] == '/page')
			$permalink = $context->getProperty('uri.blog')."/page/" . ($blog['useSloganOnPost'] ? URL::encode($entry['slogan'], $service['useEncodedURL']) : $entry['id']);
		else if (defined('__TEXTCUBE_PAGE__'))
			$permalink = $context->getProperty('uri.blog')."/" . ($blog['useSloganOnPost'] ? URL::encode($entry['slogan'],$service['useEncodedURL']) : $entry['id']);
		else
			$permalink = $context->getProperty('uri.blog')."/" . ($blog['useSloganOnPost'] ? "entry/" . URL::encode($entry['slogan'],$service['useEncodedURL']) : $entry['id']);

		if ($entry['category'] == - 1) { // This is keylog
			$entryView = $skin->keylogItem;
			dress('keylog_rep_date', fireEvent('ViewKeylogDate', Timestamp::format5($entry['published']), $entry['published']), $entryView);
			dress('keylog_rep_date_modified', fireEvent('ViewKeylogDate', Timestamp::format5($entry['modified']), $entry['modified']), $entryView);
			dress('keylog_rep_title', htmlspecialchars(fireEvent('ViewKeylogTitle', $entry['title'], $entry['id'])), $entryView);
			// 사용자가 작성한 본문은 interface/common/blog/end.php의 removeAllTags() 다음에 처리하기 위한 조치.
			$contentContainer["keylog_{$entry['id']}"] = getEntryContentView($blogid, $entry['id'], $entry['content'], $entry['contentformatter'], null, 'Keylog');
			dress('keylog_rep_desc', setTempTag("keylog_{$entry['id']}"), $entryView);
			dress('keylog_rep_author',  User::getName($entry['userid']), $entryView);
			dress('keylog_rep_author_link', $blogURL."/author/".rawurlencode(User::getName($entry['userid'])), $entryView);
			$entriesView .= $entryView;
			$isKeylog = true;
		} else if ($entry['category'] == - 2) { // This is notice
			$entryView = $skin->noticeItem;
			dress('notice_rep_microformat_published', Timestamp::getISO8601($entry['published']), $entryView);
			dress('notice_rep_microformat_updated', Timestamp::getISO8601($entry['modified']), $entryView);
			dress('notice_rep_date', fireEvent('ViewNoticeDate', Timestamp::format5($entry['published']), $entry['published']), $entryView);
			dress('notice_rep_date_modified', fireEvent('ViewNoticeDate', Timestamp::format5($entry['modified']), $entry['modified']), $entryView);
			dress('notice_rep_title', htmlspecialchars(fireEvent('ViewNoticeTitle', $entry['title'], $entry['id'])), $entryView);
			dress('notice_rep_link', $permalink, $entryView);
			
			// 사용자가 작성한 본문은 interface/common/blog/end.php의 removeAllTags() 다음에 처리하기 위한 조치.
			$contentContainer["notice_{$entry['id']}"] = getEntryContentView($blogid, $entry['id'], $entry['content'], $entry['contentformatter'], getKeywordNames($blogid), 'Notice');
			dress('notice_rep_desc', setTempTag("notice_{$entry['id']}"), $entryView);
			dress('notice_rep_author', User::getName($entry['userid']), $entryView);
			dress('notice_rep_author_link', $blogURL."/author/".rawurlencode(User::getName($entry['userid'])), $entryView);
			$entriesView .= $entryView;

		} else if ($entry['category'] == - 3) { // This is page
			$entryView = $skin->pageItem;
			dress('page_rep_microformat_published', Timestamp::getISO8601($entry['published']), $entryView);
			dress('page_rep_microformat_updated', Timestamp::getISO8601($entry['modified']), $entryView);
			dress('page_rep_date', fireEvent('ViewPageDate', Timestamp::format5($entry['published']), $entry['published']), $entryView);
			dress('page_rep_date_modified', fireEvent('ViewPageDate', Timestamp::format5($entry['modified']), $entry['modified']), $entryView);
			dress('page_rep_title', htmlspecialchars(fireEvent('ViewPageTitle', $entry['title'], $entry['id'])), $entryView);
			dress('page_rep_link', $permalink, $entryView);
			
			// 사용자가 작성한 본문은 interface/common/blog/end.php의 removeAllTags() 다음에 처리하기 위한 조치.
			$contentContainer["page_{$entry['id']}"] = getEntryContentView($blogid, $entry['id'], $entry['content'], $entry['contentformatter'], getKeywordNames($blogid), 'Page');
			dress('page_rep_desc', setTempTag("page_{$entry['id']}"), $entryView);
			dress('page_rep_author', User::getName($entry['userid']), $entryView);
			dress('page_rep_author_link', $blogURL."/author/".rawurlencode(User::getName($entry['userid'])), $entryView);
			$entriesView .= $entryView;
		} else if (doesHaveOwnership() || ($entry['visibility'] >= 2) || (isset($_COOKIE[$context->getProperty('service.cookie_prefix').'GUEST_PASSWORD']) && (trim($_COOKIE[$context->getProperty('service.cookie_prefix').'GUEST_PASSWORD']) == trim($entry['password'])))) {	// This is post
			$entryView = $skin->entry;
			$entryView = '<a id="entry_'.$entry['id'].'"></a>'.CRLF.$entryView;

			dress('tb', getTrackbacksView($entry, $skin, $entry['accepttrackback']), $entryView);
			if (!$context->getProperty('blog.showCommentBox',false) && $context->getProperty('blog.useAjaxComment',true) && !$context->getProperty('skin.expandComment', false)) {
				$commentBlockStyle = 'none';
			} else if ($context->getProperty('skin.expandComment', false) || (($context->getProperty('suri.directive','/') == '/' || $context->getProperty('suri.directive','/') == '/entry') && $context->getProperty('suri.value','') != '')) {
				$commentBlockStyle = 'block';
			} else {
				$commentBlockStyle = 'none';
			}
			
			dress('rp', "<div id=\"entry{$entry['id']}Comment\" style=\"display:$commentBlockStyle\">" .
					($commentBlockStyle == 'none' ? '' : getCommentView($entry, $skin)) .
					 "</div>", $entryView);
			
			$tagLabelView = $skin->tagLabel;
			$entryTags = getTags($entry['blogid'], $entry['id']);
			if (sizeof($entryTags) > 0) {
				$tags = array();
				$relTag = Setting::getBlogSettingGlobal('useMicroformat', 3)>1 && (count($entries) == 1 || !empty($skin->hentryExisted) );
				foreach ($entryTags as $entryTag) {
					$tags[$entryTag['name']] = "<a href=\"".$context->getProperty('uri.default')."/tag/" . (Setting::getBlogSettingGlobal('useSloganOnTag',true) ? URL::encode($entryTag['name'],$service['useEncodedURL']) : $entryTag['id']). '"' . ($relTag ? ' rel="tag"' : '') . '>' . htmlspecialchars($entryTag['name']) . '</a>';
					array_push($totalTags,$entryTag['name']);
				}
				$tags = fireEvent('ViewTagLists', $tags, $entry['id']);
				dress('tag_label_rep', implode(",\r\n", array_values($tags)), $tagLabelView);
				dress('tag_label', $tagLabelView, $entryView);
			}
			if (doesHaveOwnership() && ($entry['userid'] == getUserId() || Acl::check('group.editors')===true)) {
				$managementView = $skin->management;
				$useEncodedURL = false;
				if( isset($service['useEncodedURL'])) {
					$useEncodedURL = $service['useEncodedURL'];
				}
				dress('s_ad_m_link', $context->getProperty('uri.blog')."/owner/entry/edit/{$entry['id']}?returnURL=" . ($useEncodedURL ? $permalink : str_replace('%2F', '/', rawurlencode($permalink))), $managementView);
				dress('s_ad_m_onclick', "editEntry({$entry['id']},'".($useEncodedURL ? $permalink : str_replace('%2F', '/', rawurlencode($permalink)))."'); return false;", $managementView);
				dress('s_ad_s1_label', getEntryVisibilityName($entry['visibility']), $managementView);
				if ($entry['visibility'] < 2) {
					dress('s_ad_s2_label', _text('공개로 변경합니다'), $managementView);
					dress('s_ad_s2_onclick', "changeVisibility({$entry['id']}, 2); return false;", $managementView);
				} else {
					dress('s_ad_s2_label', _text('비공개로 변경합니다'), $managementView);
					dress('s_ad_s2_onclick', "changeVisibility({$entry['id']}, 0); return false;", $managementView);
				}
				dress('s_ad_t_onclick', "sendTrackback({$entry['id']}); return false;", $managementView);
				dress('s_ad_d_onclick', "deleteEntry({$entry['id']}); return false;", $managementView);
				dress('ad_div', $managementView, $entryView);
			}
			$author = User::getName($entry['userid']);
			dress('article_rep_author', fireEvent('ViewPostAuthor', $author, $entry['id']), $entryView);
			dress('article_rep_author_link', $blogURL."/author/".rawurlencode($author), $entryView);
			dress('article_rep_id', $entry['id'], $entryView);
			dress('article_rep_link', $permalink, $entryView);
			dress('article_rep_rp_rssurl', $context->getProperty('uri.default').'/rss/comment/'.$entry['id'], $entryView);
			dress('article_rep_tb_rssurl', $context->getProperty('uri.default').'/rss/trackback/'.$entry['id'], $entryView);
			dress('article_rep_response_rssurl', $context->getProperty('uri.default').'/rss/response/'.$entry['id'], $entryView);
			dress('article_rep_rp_atomurl', $context->getProperty('uri.default').'/atom/comment/'.$entry['id'], $entryView);
			dress('article_rep_tb_atomurl', $context->getProperty('uri.default').'/atom/trackback/'.$entry['id'], $entryView);
			dress('article_rep_response_atomurl', $context->getProperty('uri.default').'/atom/response/'.$entry['id'], $entryView);
			dress('article_rep_category_body_id',getCategoryBodyIdById($blogid,$entry['category']) ? getCategoryBodyIdById($blogid,$entry['category']) : 'tt-body-category',$entryView);
			dress('article_rep_title', htmlspecialchars(fireEvent('ViewPostTitle', $entry['title'], $entry['id'])), $entryView);
			// 사용자가 작성한 본문은 interface/common/blog/end.php의 removeAllTags() 다음에 처리하기 위한 조치.
			$contentContainer["article_{$entry['id']}"] = getEntryContentView($blogid, $entry['id'], $entry['content'], $entry['contentformatter'], getKeywordNames($blogid));
			dress('article_rep_desc', setTempTag("article_{$entry['id']}"), $entryView);
			dress('article_rep_category', htmlspecialchars(empty($entry['category']) ? _text('분류없음') : $entry['categoryLabel'], $entry['id']), $entryView);
			dress('article_rep_category_link', $context->getProperty('uri.blog')."/category/".(empty($entry['category']) ? "" : ($blog['useSloganOnCategory'] ? URL::encode($entry['categoryLabel'],$service['useEncodedURL']) : $entry['category'])),$entryView);
			dress('article_rep_category_rssurl', $context->getProperty('uri.default')."/rss/category/".(empty($entry['category']) ? "" : ($blog['useSloganOnCategory'] ? URL::encode($entry['categoryLabel'],$service['useEncodedURL']) : $entry['category'])),$entryView);
			dress('article_rep_category_atomurl', $context->getProperty('uri.default')."/atom/category/".(empty($entry['category']) ? "" : ($blog['useSloganOnCategory'] ? URL::encode($entry['categoryLabel'],$service['useEncodedURL']) : $entry['category'])),$entryView);
			dress('article_rep_microformat_published', Timestamp::getISO8601($entry['published']), $entryView);
			dress('article_rep_microformat_updated', Timestamp::getISO8601($entry['modified']), $entryView);
			dress('article_rep_date', fireEvent('ViewPostDate', Timestamp::format5($entry['published']), $entry['published']), $entryView);
			dress('article_rep_date_modified', fireEvent('ViewPostDate', Timestamp::format5($entry['modified']), $entry['modified']), $entryView);
			dress('entry_archive_link', $context->getProperty('uri.blog')."/archive/" . Timestamp::getDate($entry['published']), $entryView);
			if ($entry['acceptcomment'] || ($entry['comments'] > 0))
				dress('article_rep_rp_link', "loadComment({$entry['id']},1,false,false); return false", $entryView);
			else
				dress('article_rep_rp_link', "return false", $entryView);
		
			dress('article_rep_rp_cnt_id', "commentCount{$entry['id']}", $entryView);
			list($tempTag, $commentView) = getCommentCountPart($entry['comments'], $skin);
			dress($tempTag, $commentView, $entryView);
		
			if ($entry['accepttrackback'] || ($entry['trackbacks'] > 0))
				dress('article_rep_tb_link', "toggleLayer('entry{$entry['id']}Trackback'); return false", $entryView);
			else
				dress('article_rep_tb_link', "return false", $entryView);
		
			dress('article_rep_tb_cnt_id', "trackbackCount{$entry['id']}", $entryView);
			list($tempTag, $trackbackView) = getTrackbackCountPart($entry['trackbacks'], $skin);
			dress($tempTag, $trackbackView, $entryView);
			$entriesView .= $entryView;
		} else {	// Protected entries
			$protectedEntryView = $skin->entryProtected;
			$author = User::getName($entry['userid']);
			dress('article_rep_author', fireEvent('ViewPostAuthor', $author, $entry['id']), $protectedEntryView);
			dress('article_rep_author_link', $blogURL."/author/".rawurlencode($author), $protectedEntryView);
			dress('article_rep_id', $entry['id'], $protectedEntryView);
			dress('article_rep_link', $permalink, $protectedEntryView);
			dress('article_rep_title', htmlspecialchars(fireEvent('ViewPostTitle', $entry['title'], $entry['id'])), $protectedEntryView);
			dress('article_rep_date', fireEvent('ViewPostDate', Timestamp::format5($entry['published'])), $protectedEntryView);
			dress('article_password', "entry{$entry['id']}password", $protectedEntryView);
			dress('article_dissolve', "reloadEntry({$entry['id']});", $protectedEntryView);
			if (isset($_POST['partial']))
				$entriesView .= $protectedEntryView;
			else
				$entriesView .= "<div id=\"entry{$entry['id']}\">$protectedEntryView</div>";
		}
	}
	if(count($entries) > 1 || (count($entries) == 1 && empty($suri['value']))) {
		unset($totalTags);
	}
	if(count($entries) == 1) {	// Adds trackback RDF
		$info = array();
		$info['title']        = htmlspecialchars($entries[0]['title']);
		$info['permalink']    = $permalink;
		$info['trackbackURL'] = $context->getProperty('uri.default')."/trackback/".$entries[0]['id'];
		$entriesView .= getTrackbackRDFView($blogid, $info);
	}
	if(isset($cache)) {
		$cache->contents = revertTempTags(removeAllTags($entriesView));
		if(isset($paging)) $cache->dbContents = $paging;
		$cache->update();
	}
}
$view = str_replace( "[##_article_rep_##]", "<div class=\"hfeed\">[##_article_rep_##]</div>", $view);
if(isset($isKeylog) && $isKeylog) {
	dressInsertBefore('list', $entriesView, $view);
	$isKeylog = false;
} else {
	if (isset($cache->contents)) {
		dressInsertBefore('article_rep', $entriesView, $view);
	}else{
		dress('article_rep', $entriesView, $view);
	}
}
?>
