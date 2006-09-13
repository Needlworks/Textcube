<?php

class Skin {
	var $outter;
	var $skin;
	var $list;
	var $listItem;
	var $commentList;
	var $commentListItem;
	var $rss;
	var $rss_date;
	var $rss_rep;
	var $keywordItem;
	var $keywordGroup;
	var $keyword;
	var $noticeItem;
	var $recentNotice;
	var $recentNoticeItem;
	var $locative;
	var $locativeSpot;
	var $locativeEntry;
	var $guest;
	var $guestForm;
	var $guestGuest;
	var $guestMember;
	var $guestContainer;
	var $guestItem;
	var $guestSubContainer;
	var $guestSubItem;
	var $search;
	var $recentEntry;
	var $recentComments;
	var $recentTrackback;
	var $management;
	var $entryProtected;
	var $entry;
	var $paging;
	var $pagingItem;
	var $archive;
	var $s_ps;
	var $s_ps_rep;
	var $comment;
	var $commentCount;
	var $commentForm;
	var $commentGuest;
	var $commentMember;
	var $commentContainer;
	var $commentItem;
	var $commentSubContainer;
	var $commentSubItem;
	var $trackbacks;
	var $trackbackContainer;
	var $trackback;
	var $trackbackCount;
	var $tagLabel;
	var $siteTag;
	var $siteTagItem;
	var $randomTags;
	var $s_link_rep;
	var $aux;
	var $sidebar;
	var $sidebarElement;
	var $inlineSidebarCount = 0;
	
	
	var $noneCommentMessage;
	var $singleCommentMessage;
	var $noneTrackbackMessage;
	var $singleTrackbackMessage;
	
	
	function Skin($name) {
		global $service, $blogURL;
		global $owner;
		
		$this->noneCommentMessage = getUserSetting('noneCommentMessage');
		$this->singleCommentMessage = getUserSetting('singleCommentMessage');
		$this->noneTrackbackMessage = getUserSetting('noneTrackbackMessage');
		$this->singleTrackbackMessage = getUserSetting('singleTrackbackMessage');
		
		
		if (strncmp($name, 'customize/', 10) == 0) {
			$name = "customize/$owner";
		} else {
			$name = Path::getBaseName($name);
		}
		
		if (($name == '.') || ($name == '..')) {
			respondErrorPage(_text('스킨 정보가 존재하지 않습니다.'), _text('로그인'), $blogURL."/owner");
		}
		
		$filename = ROOT . "/skin/$name/skin.html";
		
		if (!is_file($filename)) {
			respondErrorPage(_text('스킨 정보가 존재하지 않습니다.'), _text('로그인'), $blogURL."/owner");
		}
		
		if (!$sval = file_get_contents($filename))
			respondErrorPage(_text('스킨 정보가 존재하지 않습니다.'), _text('로그인'), $blogURL."/owner");

		$sval = replaceSkinTag($sval, 'html');
		$sval = replaceSkinTag($sval, 'head');
		$sval = replaceSkinTag($sval, 'body');
		handleTags($sval);
		
		list($sval, $this->sidebar) = $this->cutSkinTag($sval, 'sidebar');
		list($this->sidebar, $this->sidebarElement) = $this->cutSkinTagForSidebar($this->sidebar, 'sidebar_element');
		list($this->sidebar, $this->sidebarTitles) = $this->cutSkinTag($this->sidebar, 'sidebar_titles');
		list($this->sidebar, $this->sidebarItem) = $this->cutSkinTag($this->sidebar, 'sidebar_rep_element');
		handleSidebars($sval, $this);

		$sval = str_replace('./', "{$service['path']}/skin/$name/", $sval);
		list($sval, $this->listItem) = $this->cutSkinTag($sval, 'list_rep');
		list($sval, $this->list) = $this->cutSkinTag($sval, 'list');
		list($sval, $this->commentListItem) = $this->cutSkinTag($sval, 'rplist_rep');
		list($sval, $this->commentList) = $this->cutSkinTag($sval, 'rplist');
		list($sval, $this->rss_rep) = $this->cutSkinTag($sval, 'rss_rep');
		list($sval, $this->rss_date) = $this->cutSkinTag($sval, 'rss_date');
		list($sval, $this->rss) = $this->cutSkinTag($sval, 'rss');
		list($sval, $this->keywordItem) = $this->cutSkinTag($sval, 'keyword_rep');
		list($sval, $this->keywordGroup) = $this->cutSkinTag($sval, 'keyword_date_rep');
		list($sval, $this->keyword) = $this->cutSkinTag($sval, 'keyword');
		list($sval, $this->noticeItem) = $this->cutSkinTag($sval, 'notice_rep');
		list($sval, $this->recentNoticeItem) = $this->cutSkinTag($sval, 'rct_notice_rep');
		list($sval, $this->recentNotice) = $this->cutSkinTag($sval, 'rct_notice');
		list($sval, $this->locativeEntry) = $this->cutSkinTag($sval, 'local_info_rep');
		list($sval, $this->locativeSpot) = $this->cutSkinTag($sval, 'local_spot_rep');
		list($sval, $this->locative) = $this->cutSkinTag($sval, 'local');

		list($sval, $this->guestSubContainer) = $this->cutSkinTag($sval, 'guest_reply_container');
		if ($this->guestSubContainer == '') {
			$this->guestSubContainer = '[##_guest_reply_rep_##]';
			list($sval, $this->guestSubItem) = $this->cutSkinTag($sval, 'guest_reply_rep', '[##_guest_reply_container_##]');
		} else {
			list($this->guestSubContainer, $this->guestSubItem) = $this->cutSkinTag($this->guestSubContainer, 'guest_reply_rep');
		}
		list($sval, $this->guestContainer) = $this->cutSkinTag($sval, 'guest_container');
		if ($this->guestContainer == '') {
			$this->guestContainer = '[##_guest_rep_##]';
			list($sval, $this->guestItem) = $this->cutSkinTag($sval, 'guest_rep', '[##_guest_container_##]');
		} else {
			list($this->guestContainer, $this->guestItem) = $this->cutSkinTag($this->guestContainer, 'guest_rep');
		}
		
		list($sval, $this->guestGuest) = $this->cutSkinTag($sval, 'guest_form');
		list($sval, $this->guestMember) = $this->cutSkinTag($sval, 'guest_member');
		list($sval, $this->guestForm) = $this->cutSkinTag($sval, 'guest_input_form');
		list($sval, $this->guest) = $this->cutSkinTag($sval, 'guest');
		list($sval, $this->entryProtected) = $this->cutSkinTag($sval, 'article_protected');
		list($sval, $this->management) = $this->cutSkinTag($sval, 'ad_div');
		list($sval, $this->trackbackCount) = $this->cutSkinTag($sval, 'tb_count');
		if ($this->trackbackCount == '') {
			list($sval, $this->trackbackCount) = $this->cutSkinReplacer($sval, 'article_rep_tb_cnt','[##_tb_count_##]');
			$this->noneTrackbackMessage = '';
			$this->singleTrackbackMessage = '';
		}
		list($sval, $this->commentCount) = $this->cutSkinTag($sval, 'rp_count');
		if ($this->commentCount == '') {
			list($sval, $this->commentCount) = $this->cutSkinReplacer($sval, 'article_rep_rp_cnt','[##_rp_count_##]');
			$this->noneCommentMessage = '';
			$this->singleCommentMessage = '';
		}
		
		list($sval, $this->trackbackContainer) = $this->cutSkinTag($sval, 'tb_container');
		if ($this->trackbackContainer == '') {
			$this->trackbackContainer = '[##_tb_rep_##]';
			list($sval, $this->trackback) = $this->cutSkinTag($sval, 'tb_rep', '[##_tb_container_##]');
		} else {
			list($this->trackbackContainer, $this->trackback) = $this->cutSkinTag($this->trackbackContainer, 'tb_rep');
		}		
		
		list($sval, $this->trackbacks) = $this->cutSkinTag($sval, 'tb');
		list($sval, $this->tagLabel) = $this->cutSkinTag($sval, 'tag_label');
		list($sval, $this->siteTagItem) = $this->cutSkinTag($sval, 'tag_rep');
		list($sval, $this->siteTag) = $this->cutSkinTag($sval, 'tag');
		list($sval, $this->randomTags) = $this->cutSkinTag($sval, 'random_tags');
		
		list($sval, $this->commentSubContainer) = $this->cutSkinTag($sval, 'rp2_container');
		if ($this->commentSubContainer == '') {
			$this->commentSubContainer = '[##_rp2_rep_##]';
			list($sval, $this->commentSubItem) = $this->cutSkinTag($sval, 'rp2_rep', '[##_rp2_container_##]');
		} else {
			list($this->commentSubContainer, $this->commentSubItem) = $this->cutSkinTag($this->commentSubContainer, 'rp2_rep');
		}
		list($sval, $this->commentContainer) = $this->cutSkinTag($sval, 'rp_container');
		if ($this->commentContainer == '') {
			$this->commentContainer = '[##_rp_rep_##]';
			list($sval, $this->commentItem) = $this->cutSkinTag($sval, 'rp_rep', '[##_rp_container_##]');
		} else {
			list($this->commentContainer, $this->commentItem) = $this->cutSkinTag($this->commentContainer, 'rp_rep');
		}
		
		list($sval, $this->commentGuest) = $this->cutSkinTag($sval, 'rp_guest');
		list($sval, $this->commentMember) = $this->cutSkinTag($sval, 'rp_member');
		list($sval, $this->commentForm) = $this->cutSkinTag($sval, 'rp_input_form');
		list($sval, $this->comment) = $this->cutSkinTag($sval, 'rp');
		
		
		list($sval, $this->entry) = $this->cutSkinTag($sval, 'article_rep');
		list($sval, $this->pagingItem) = $this->cutSkinTag($sval, 'paging_rep');
		list($sval, $this->paging) = $this->cutSkinTag($sval, 'paging');
		list($sval, $this->archive) = $this->cutSkinTag($sval, 'archive_rep');
		list($sval, $this->search) = $this->cutSkinTag($sval, 'search');
		list($sval, $this->recentEntry) = $this->cutSkinTag($sval, 'rctps_rep');
		list($sval, $this->recentComments) = $this->cutSkinTag($sval, 'rctrp_rep');
		list($sval, $this->recentTrackback) = $this->cutSkinTag($sval, 'rcttb_rep');
		list($sval, $this->s_link_rep) = $this->cutSkinTag($sval, 'link_rep');
		list($sval, $this->skin) = $this->cutSkinTag($sval, 't3');
		$this->outter = $sval;
	}
	
	function cutSkinTag($contents, $tag, $replace = null) {
		if (is_null($replace)) {
			$replace = "[##_{$tag}_##]";
		}
		$tagSize = strlen($tag) + 4;
		$begin = strpos($contents, "<s_$tag>");
		if ($begin === false)
			return array($contents, '');
		$end = strpos($contents, "</s_$tag>", $begin + 4);
		if ($end === false)
			return array($contents, '');
		$inner = substr($contents, $begin + $tagSize, $end - $begin - $tagSize);
		$outter = substr($contents, 0, $begin) . $replace . substr($contents, $end + $tagSize + 1);
		return array($outter, $inner);
	}
	
	function cutSkinReplacer($contents, $tag, $replace = null) {
		if (is_null($replace)) {
			$replace = "[##_{$tag}_##]";
		}
		$tagSize = strlen("[##_{$tag}_##]");
		$pos = strpos($contents, "[##_{$tag}_##]");
		if ($pos === false) {
			return array($contents, '');
		}
		$inner = "[##_{$tag}_##]";
		$outter = substr($contents, 0, $pos) . $replace . substr($contents, $pos + $tagSize);
		return array($outter, $inner);
	}
	
	function cutSkinTagForSidebar($contents, $tag) {
		$sidebarElement = array();
		$innerSidebarModules = getBasicSidebarList();
		$tempList = split("<s_$tag>|</s_$tag>", $contents);
		
		for ($i=0; $i<count($tempList); $i++) {
			if (($i % 2) == 1) {
				if (ereg("\[##_image_##\]", $tempList[$i])) {
					$id = "%BlogLogo%";
				} else if (ereg("\[##_search_onclick_submit_##\]", $tempList[$i])) {
					$id = "%Search%";
				} else if (ereg("\[##_category_##\]", $tempList[$i])) {
					// 사이드바 플러그인의 id는 디렉토리명이기 때문에 %를 사용할 수 없다($IV 체크에 의거).
					// 따라서 내장 모듈에 %를 사용하면 unique 값이 된다(=중복선언의 위험이 없다).
					$id = "%Category%";
				} else if (ereg("\[##_category_list_##\]", $tempList[$i])) {
					$id = "%CategoryList%";
				} else if (ereg("\[##_calendar_##\]", $tempList[$i])) {
					$id = "%Calendar%";
				} else if (ereg("<s_random_tags>", $tempList[$i])) {
					$id = "%TagList%";
				} else if (ereg("<s_rctps_rep>", $tempList[$i])) {
					$id = "%RecentPosts%";
				} else if (ereg("<s_rcttb_rep>", $tempList[$i])) {
					$id = "%RecentTrackback%";
				} else if (ereg("<s_rctrp_rep>", $tempList[$i])) {
					$id = "%RecentComment%";
				} else if (ereg("<s_archive_rep>", $tempList[$i])) {
					$id = "%RecentArchive%";
				} else if (ereg("<s_link_rep>", $tempList[$i])) {
					$id = "%Link%";
				} else if (ereg("\[##_count_today_##\]", $tempList[$i])) {
					$id = "%Counter%";
				} else {
					$tempList[$i] = "<s_{$tag}>{$tempList[$i]}</s_{$tag}>";
					continue;
				}
				
				$sidebarElement[$id][0] = $innerSidebarModules[$id]['title'];
				$sidebarElement[$id][1] = $tempList[$i];
				$tempList[$i] = "[##_sidebar_module_{$this->inlineSidebarCount}_##]";
				$this->inlineSidebarCount++;
			}
		}
		
		return array(implode("", $tempList), $sidebarElement);
	}
}

class KeywordSkin {
	var $outter;
	var $skin;
	var $keylog;
	var $keylogItem;

	function KeywordSkin($name) {
		global $service;
		$filename = ROOT . "/skin/$name/skin_keyword.html";
		if (!$sval = file_get_contents($filename))
			respondErrorPage("KeywordSkin");
		$sval = str_replace('./', "{$service['path']}/skin/$name/", $sval);
		list($sval, $this->keylogItem) = $this->cutSkinTag($sval, 'blog_rep');
		list($sval, $this->keylog) = $this->cutSkinTag($sval, 'blog');
		list($sval, $this->skin) = $this->cutSkinTag($sval, 't3');
		$this->outter = $sval;
	}
	
	function cutSkinTag($contents, $tag) {
		$tagSize = strlen($tag) + 4;
		$begin = strpos($contents, "<s_$tag>");
		if ($begin === false)
			return array($contents, '');
		$end = strpos($contents, "</s_$tag>", $begin + 5);
		if ($end === false)
			return array($contents, '');
		$inner = substr($contents, $begin + $tagSize, $end - $begin - $tagSize);
		$outter = substr($contents, 0, $begin) . "[##_{$tag}_##]" . substr($contents, $end + $tagSize + 1);
		return array($outter, $inner);
	}
}

function removeAllTags($contents) {
	return preg_replace('/\[#M_[^|]*\|[^|]*\|/Us', '', str_replace('_M#]', '', preg_replace('/\[##_.+_##\]/Us', '', $contents)));
}

function replaceSkinTag($contents, $tag) {
	$pattern[] = '/(<'.$tag.'.*>)\r?\n/Ui';
	$pattern[] = '/<\/'.$tag.'>/Ui';

	$replacement[] = '$1'.CRLF.'[##_SKIN_'.$tag.'_start_##]';
	$replacement[] = '[##_SKIN_'.$tag.'_end_##]$0';

	return preg_replace($pattern, $replacement, $contents);
}
?>
