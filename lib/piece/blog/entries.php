<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$entriesView = '';

if(isset($entries)) {
	foreach ($entries as $entry) {
		if ($suri['directive'] == '/notice')
			$permalink = "$blogURL/notice/{$entry['id']}";
		else if ($suri['directive'] == '/page')
			$permalink = "$blogURL/page/{$entry['id']}";
		else
			$permalink = "$blogURL/" . ($blog['useSlogan'] ? "entry/" . encodeURL($entry['slogan']) : $entry['id']);
		if ($entry['category'] == - 2) {
			$entryView = $skin->noticeItem;
			dress('notice_rep_date', fireEvent('ViewNoticeDate', Timestamp::format5($entry['published'])), $entryView);
			dress('notice_rep_title', htmlspecialchars(fireEvent('ViewNoticeTitle', $entry['title'], $entry['id'])), $entryView);
			if ($suri['directive'] == '/notice')
				dress('notice_rep_link', "$blogURL/notice/{$entry['id']}", $entryView);
			else
				dress('notice_rep_link', "$blogURL/page/{$entry['id']}", $entryView);
			
			// 사용자가 작성한 본문은 lib/piece/blog/end.php의 removeAllTags() 다음에 처리하기 위한 조치.
			$contentContainer["notice_{$entry['id']}"] = getEntryContentView($owner, $entry['id'], $entry['content'], $entry['contentFormatter'], getKeywordNames($owner), 'Notice');
			dress('notice_rep_desc', setTempTag("notice_{$entry['id']}"), $entryView);
			$entriesView .= $entryView;
		} else if (doesHaveOwnership() || ($entry['visibility'] >= 2) || (isset($_COOKIE['GUEST_PASSWORD']) && (trim($_COOKIE['GUEST_PASSWORD']) == trim($entry['password'])))) {
			$entryView = $skin->entry;
			dress('tb', getTrackbacksView($entry['id'], $skin, $entry['acceptTrackback']), $entryView);
			if ($skinSetting['expandComment'] == 1 || (($suri['url'] != $blogURL.'/index.php' && $suri['url'] != $service['path'].'/index.php') && ($suri['directive'] == '/' || $suri['directive'] == '/entry') && $suri['value'] != '')) {
				$style = 'block';
			} else {
				$style = 'none';
			}
			dress('rp', "<div id=\"entry{$entry['id']}Comment\" style=\"display:$style\">" . getCommentView($entry['id'], $skin) . "</div>", $entryView);
			$tagLabelView = $skin->tagLabel;
			$entryTags = getTags($entry['id']);
			if (sizeof($entryTags) > 0) {
				$tags = array();
				foreach ($entryTags as $entryTag) {
					$tags[$entryTag['name']] = "<a href=\"$defaultURL/tag/" . encodeURL($entryTag['name']) . '"' . ((count($entries) == 1 && getUserSetting('useRelTag', true)) ? ' rel="tag"' : '') . '>' . htmlspecialchars($entryTag['name']) . '</a>';
				}
				$tags = fireEvent('ViewTagLists', $tags, $entry['id']);
				dress('tag_label_rep', implode(",\r\n", array_values($tags)), $tagLabelView);
				dress('tag_label', $tagLabelView, $entryView);
			}
			$name = teamblogUser::name();
			if (doesHaveOwnership() && !empty($name[2])) {
				$managementView = $skin->management;
				dress('s_ad_m_link', "$blogURL/owner/entry/edit/{$entry['id']}?returnURL=" . (@$service['useEncodedURL'] ? $permalink : str_replace('%2F', '/', rawurlencode($permalink))), $managementView);
				dress('s_ad_m_onclick', "editEntry({$entry['id']},'".(@$service['useEncodedURL'] ? $permalink : str_replace('%2F', '/', rawurlencode($permalink)))."'); return false;", $managementView);
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
			if(!empty($name[3])) dress('teamblog_name',$name[3],$entryView);
			dress('article_rep_id', $entry['id'], $entryView);
			dress('article_rep_link', $permalink, $entryView);
			dress('article_rep_title', htmlspecialchars(fireEvent('ViewPostTitle', $entry['title'], $entry['id'])).$name[1], $entryView);
			// 사용자가 작성한 본문은 lib/piece/blog/end.php의 removeAllTags() 다음에 처리하기 위한 조치.
			$contentContainer["article_{$entry['id']}"] = getEntryContentView($owner, $entry['id'], $entry['content'], $entry['contentFormatter'], getKeywordNames($owner));
			dress('article_rep_desc', setTempTag("article_{$entry['id']}"), $entryView);
			dress('article_rep_category', htmlspecialchars(empty($entry['category']) ? _text('분류없음') : $entry['categoryLabel'], $entry['id']), $entryView);
			dress('article_rep_category_link', empty($entry['category']) ? "$blogURL/category/" : "$blogURL/category/".encodeURL($entry['categoryLabel']) ,$entryView);
			dress('article_rep_date', fireEvent('ViewPostDate', Timestamp::format5($entry['published'])).$name[0], $entryView);
			dress('entry_archive_link', "$blogURL/archive/" . Timestamp::getDate($entry['published']), $entryView);
			if ($entry['acceptComment'] || ($entry['comments'] > 0))
				dress('article_rep_rp_link', "toggleLayer('entry{$entry['id']}Comment'); return false", $entryView);
			else
				dress('article_rep_rp_link', "return false", $entryView);
		
			dress('article_rep_rp_cnt_id', "commentCount{$entry['id']}", $entryView);
			list($tempTag, $commentView) = getCommentCountPart($entry['comments'], $skin);
			dress($tempTag, $commentView, $entryView);
		
			if ($entry['acceptTrackback'] || ($entry['trackbacks'] > 0))
				dress('article_rep_tb_link', "toggleLayer('entry{$entry['id']}Trackback'); return false", $entryView);
			else
				dress('article_rep_tb_link', "return false", $entryView);
		
			dress('article_rep_tb_cnt_id', "trackbackCount{$entry['id']}", $entryView);
			list($tempTag, $trackbackView) = getTrackbackCountPart($entry['trackbacks'], $skin);
			dress($tempTag, $trackbackView, $entryView);
	
			$entriesView .= $entryView;
		} else {
			$name = teamblogUser::name();
			if(!empty($name[3])) dress('teamblog_name',$name[3],$entryView);
			$protectedEntryView = $skin->entryProtected;
			dress('article_rep_id', $entry['id'], $protectedEntryView);
			dress('article_rep_link', $permalink, $protectedEntryView);
			dress('article_rep_title', htmlspecialchars(fireEvent('ViewPostTitle', $entry['title'], $entry['id'])).$name[1], $protectedEntryView);
			dress('article_rep_date', fireEvent('ViewPostDate', Timestamp::format5($entry['published'])).$name[0], $protectedEntryView);
			dress('article_password', "entry{$entry['id']}password", $protectedEntryView);
			dress('article_dissolve', "reloadEntry({$entry['id']});", $protectedEntryView);
			if (isset($_POST['partial']))
				$entriesView .= $protectedEntryView;
			else
				$entriesView .= "<div id=\"entry{$entry['id']}\">$protectedEntryView</div>";
		}
	}
}
dress('article_rep', $entriesView, $view);
?>
