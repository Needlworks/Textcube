<?php
$entriesView = '';

foreach ($entries as $entry) {
	if ($entry['category'] == - 2) {
		$entryView = $skin->noticeItem;
		dress('notice_rep_date', fireEvent('ViewNoticeDate', Timestamp::format5($entry['published'])), $entryView);
		dress('notice_rep_title', htmlspecialchars(fireEvent('ViewNoticeTitle', $entry['title'], $entry['id'])), $entryView);
		dress('notice_rep_link', "$blogURL/notice/{$entry['id']}", $entryView);
		dress('notice_rep_desc', getEntryContentView($owner, $entry['id'], $entry['content'], getKeywordNames($owner), 'Notice'), $entryView);
		$entriesView .= $entryView;
	} else if (doesHaveOwnership() || ($entry['visibility'] >= 2) || (isset($_COOKIE['GUEST_PASSWORD']) && ($_COOKIE['GUEST_PASSWORD'] == $entry['password']))) {
		$entryView = $skin->entry;
		dress('tb', getTrackbacksView($entry['id'], $skin), $entryView);
		if ($skinSetting['expandComment'] == 1 || (($suri['url'] != $blogURL.'/index.php' && $suri['url'] != $service['path'].'/index.php') && ($suri['directive'] == '/' || $suri['directive'] == '/entry') && $suri['value'] != '')) {
			$style = 'block';
		} else {
			$style = 'none';
		}
		dress('rp', "<div id=\"entry{$entry['id']}Comment\" style=\"display:$style\">" . getCommentView($entry['id'], $skin) . '</div>', $entryView);
		$tagLabelView = $skin->tagLabel;
		$entryTags = getTags($entry['id']);
		if (sizeof($entryTags) > 0) {
			$tags = array();
			foreach ($entryTags as $entryTag)
				array_push($tags, "<a href=\"$blogURL/tag/" . encodeURL($entryTag['name']) . '">' . htmlspecialchars($entryTag['name']) . '</a>');
			dress('tag_label_rep', implode(",\r\n", $tags), $tagLabelView);
			dress('tag_label', $tagLabelView, $entryView);
		}
		if ($suri['directive'] == '/notice')
			$permalink = "$blogURL/notice/{$entry['id']}";
		else
			$permalink = "$blogURL/" . ($blog['useSlogan'] ? "entry/" . encodeURL($entry['slogan']) : $entry['id']);
		if (doesHaveOwnership()) {
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
		dress('article_rep_id', $entry['id'], $entryView);
		dress('article_rep_link', $permalink, $entryView);
		dress('article_rep_title', htmlspecialchars(fireEvent('ViewPostTitle', $entry['title'], $entry['id'])), $entryView);
		dress('article_rep_desc', getEntryContentView($owner, $entry['id'], $entry['content'], getKeywordNames($owner)), $entryView);
		dress('article_rep_category', htmlspecialchars(empty($entry['category']) ? _text('분류없음') : $entry['categoryLabel'], $entry['id']), $entryView);
		dress('article_rep_category_link', "$blogURL/category/" . encodeURL($entry['categoryLabel']), $entryView);
		dress('article_rep_date', fireEvent('ViewPostDate', Timestamp::format5($entry['published'])), $entryView);
		dress('entry_archive_link', "$blogURL/archive/" . Timestamp::getDate($entry['published']), $entryView);
		if ($entry['acceptComment'])
			dress('article_rep_rp_link', "toggleLayer('entry{$entry['id']}Comment'); return false", $entryView);
		else
			dress('article_rep_rp_link', "alert('" . _text('이 글에는 댓글을 달 수 없습니다.') . "'); return false", $entryView);
		list($tempTag, $commentView) = getCommentCountPart($entry['comments'], $skin);
		dress($tempTag, "<span id=\"commentCount{$entry['id']}\">{$commentView}</span>", $entryView);
		
		if ($entry['acceptTrackback'])
			dress('article_rep_tb_link', "toggleLayer('entry{$entry['id']}Trackback'); return false", $entryView);
		else
			dress('article_rep_tb_link', "alert('" . _text('이 글에는 트랙백을 달 수 없습니다.') . "'); return false", $entryView);
		list($tempTag, $trackbackView) = getTrackbackCountPart($entry['trackbacks'], $skin);
		dress($tempTag, $trackbackView, $entryView);
		
		$entriesView .= $entryView;
	} else {
		$protectedEntryView = $skin->entryProtected;
		dress('article_rep_id', $entry['id'], $protectedEntryView);
		dress('article_rep_title', htmlspecialchars(fireEvent('ViewPostTitle', $entry['title'], $entry['id'])), $protectedEntryView);
		dress('article_rep_date', fireEvent('ViewPostDate', Timestamp::format5($entry['published'])), $protectedEntryView);
		dress('article_password', "entry{$entry['id']}password", $protectedEntryView);
		dress('article_dissolve', "reloadEntry({$entry['id']})", $protectedEntryView);
		if (isset($_POST['partial']))
			$entriesView .= $protectedEntryView;
		else
			$entriesView .= "<div id=\"entry{$entry['id']}\">$protectedEntryView</div>";
	}
}
dress('article_rep', $entriesView, $view);
?>
