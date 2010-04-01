<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

global $__gDressTags;

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
	var $pageError;
	var $pageTitle;
	var $aux;
	var $sidebarBasicModules = array();
	var $sidebarStorage = array();
	var $sidebarOriginalContent = array();
	var $sidebarName = array();

	var $cover;
	var $coverpage;
	var $coverpageBasicModules = array();
	var $coverpageStorage = array();
	var $coverpageOriginalContent = array();
	var $coverpageName = array();
	
	var $line;
	var $lineItem;
	var $lineButton;

	var $noneCommentMessage;
	var $singleCommentMessage;
	var $noneTrackbackMessage;
	var $singleTrackbackMessage;

	var $microformatDebug;
	var $hentryExisted;

	var $dressTags = array();

	function __construct($name, $previewMode = false) {
		global $service, $blogURL, $suri, $blog, $__gDressTags, $serviceURL;
		$this->cache = new pageCache;
		$this->cache->reset('skinCache');
		$__gDressTags = array();
		if($previewMode == true || ($service['skincache'] != true) || !$this->loadCache()) {
			$this->noneCommentMessage = Setting::getBlogSettingGlobal('noneCommentMessage',null);
			$this->singleCommentMessage = Setting::getBlogSettingGlobal('singleCommentMessage',null);
			$this->noneTrackbackMessage = Setting::getBlogSettingGlobal('noneTrackbackMessage',null);
			$this->singleTrackbackMessage = Setting::getBlogSettingGlobal('singleTrackbackMessage',null);
			//$this->noneCommentMessage = $blog['noneCommentMessage'];
			//$this->singleCommentMessage = $blog['singleCommentMessage'];
			//$this->noneTrackbackMessage = $blog['noneTrackbackMessage'];
			//$this->singleTrackbackMessage = $blog['singleTrackbackMessage'];
			$this->microformatDebug = array();
			
			if (strncmp($name, 'customize/', 10) == 0) {
				$name = "customize/".getBlogId();
			} else {
				$name = Path::getBaseName($name);
			}
			
			if (($name == '.') || ($name == '..')) {
				Respond::ErrorPage(_text('스킨 정보가 존재하지 않습니다.'), _text('로그인'), $blogURL."/owner");
			}
			
			$filename = ROOT . "/skin/blog/$name/skin.html";
			
			if (!is_file($filename)) {
				Respond::ErrorPage(_text('스킨 정보가 존재하지 않습니다.'), _text('로그인'), $blogURL."/owner");
			}
			
			if (!$sval = file_get_contents($filename))
				Respond::ErrorPage(_text('스킨 정보가 존재하지 않습니다.'), _text('로그인'), $blogURL."/owner");
	
			replaceSkinTag($sval, 'html');
			replaceSkinTag($sval, 'head');
			replaceSkinTag($sval, 'body');
			insertGeneratorVersion($sval);
			
			$sval = str_replace('<s_t3>','',$sval);	// Prune s_t3. For Skin compatibility with < Textcube 1.7
			$sval = str_replace('</s_t3>','',$sval);

			// Static patch. (To increase speed)
			dressStaticElements($sval);
			
			// 사이드바 작업.
			$sidebarCount = 0;
			$noNameCount = 1;
			// - 사이드바가 여러개일 수 있으므로 루프로 돌린다.
			while (strpos($sval, '<s_sidebar>') !== false) {
				if (!isset($this->sidebarBasicModules[$sidebarCount]))
					$this->sidebarBasicModules[$sidebarCount] = array();
				list($sval, $this->sidebarOriginalContent[$sidebarCount]) = $this->cutSkinTag($sval, "sidebar", "[##_sidebar_{$sidebarCount}_##]");
				
				$moduleCount = 0;
				$matchcount = preg_match_all('@<s_sidebar_element>.*</s_sidebar_element>@isU', $this->sidebarOriginalContent[$sidebarCount], $matches);
				if ($matchcount !== false) {
					$rgSidebarContent = $matches[0];	
				} else {
					$rgSidebarContent = array();
				}
				
				for ($i=0; $i<count($rgSidebarContent); $i++) {
					$taglength = 19; //strlen('<s_sidebar_element>');
					$rgSidebarContent[$i] = substr($rgSidebarContent[$i], $taglength, strlen($rgSidebarContent[$i]) - 39);//2*$taglength - 1);
					// - 각 모듈을 나중에 가져다 쓰기 위해 기본 모듈 배열 안에 저장한다.
					preg_match("/<!\\-\\-(.+)\\-\\->/", $rgSidebarContent[$i], $temp);
					if (isset($temp[1])) {
						$tempTitle = trim($temp[1]);
					} else {
						$tempTitle = _f('(이름 없음 %1)', $noNameCount); //$rgSidebarContent[$i];
						$noNameCount++;
					}
					$this->sidebarBasicModules[$sidebarCount][$moduleCount] = array('title' => $tempTitle, 'body' => $rgSidebarContent[$i]);
					$moduleCount++;
				}
				$matchcount = preg_match('@<s_sidebar_element>@', $this->sidebarOriginalContent[$sidebarCount],$matches, PREG_OFFSET_CAPTURE);
				if (($matchcount === false) || ($matchcount == 0)) {
					$firstPos = strlen($this->sidebarOriginalContent[$sidebarCount]);
				} else {
					$firstPos = $matches[0][1];
				}
				preg_match("/<!\\-\\-(.+)\\-\\->/", substr($this->sidebarOriginalContent[$sidebarCount],0,$firstPos - 1), $temp);
				if (isset($temp[1])) {
					$tempTitle = trim($temp[1]);
				} else {
					$tempTitle = _t('사이드바') . ' ' . ($sidebarCount + 1);
				}
				$this->sidebarName[$sidebarCount] = $tempTitle;
				$sidebarCount++;
			}
	
			handleSidebars($sval, $this, $previewMode);
	
			// 표지 작업.
			$this->coverpageBasicModules[0] = array();
			$this->coverpageName[0] =_t('표지');
	
			$sval = str_replace('./', "{$serviceURL}/skin/blog/$name/", $sval);
	
			$this->noneCommentMessage = str_replace('./', "{$serviceURL}/skin/blog/$name/", $this->noneCommentMessage);
			$this->singleCommentMessage = str_replace('./', "{$serviceURL}/skin/blog/$name/", $this->singleCommentMessage);
			$this->noneTrackbackMessage = str_replace('./', "{$serviceURL}/skin/blog/$name/", $this->noneTrackbackMessage);
			$this->singleTrackbackMessage = str_replace('./', "{$serviceURL}/skin/blog/$name/", $this->singleTrackbackMessage);
	
			// Store skin tags.
			$__gDressTags = $this->getDressTags($sval);
			// Cutting skin.
			list($sval, $this->coverItem) = $this->cutSkinTag($sval, 'cover_rep');
			list($sval, $this->cover) = $this->cutSkinTag($sval, 'cover');
			
			list($sval, $this->lineItem) = $this->cutSkinTag($sval, 'line_rep');
			list($sval, $this->lineButton) = $this->cutSkinTag($sval, 'line_button');
			list($sval, $this->line) = $this->cutSkinTag($sval, 'line');
	
			list($sval, $this->listItem) = $this->cutSkinTag($sval, 'list_rep');
			list($sval, $this->list) = $this->cutSkinTag($sval, 'list');

			list($sval, $this->commentListItem) = $this->cutSkinTag($sval, 'rplist_rep');
			list($sval, $this->commentList) = $this->cutSkinTag($sval, 'rplist');

			list($sval, $this->trackbackListItem) = $this->cutSkinTag($sval, 'tblist_rep');
			list($sval, $this->trackbackList) = $this->cutSkinTag($sval, 'tblist');

			list($sval, $this->rss_rep) = $this->cutSkinTag($sval, 'rss_rep');
			list($sval, $this->rss_date) = $this->cutSkinTag($sval, 'rss_date');
			list($sval, $this->rss) = $this->cutSkinTag($sval, 'rss');

			list($sval, $this->keywordItem) = $this->cutSkinTag($sval, 'keyword_rep');
			list($sval, $this->keywordGroup) = $this->cutSkinTag($sval, 'keyword_date_rep');
			list($sval, $this->keyword) = $this->cutSkinTag($sval, 'keyword');

			list($sval, $this->noticeItem) = $this->cutSkinTag($sval, 'notice_rep');
			list($sval, $this->keylogItem) = $this->cutSkinTag($sval, 'keylog_rep');
			list($sval, $this->recentNoticeItem) = $this->cutSkinTag($sval, 'rct_notice_rep');
			list($sval, $this->recentNotice) = $this->cutSkinTag($sval, 'rct_notice');
			list($sval, $this->recentPageItem) = $this->cutSkinTag($sval, 'rct_page_rep');
			list($sval, $this->recentPage) = $this->cutSkinTag($sval, 'rct_page');
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
			
			list($sval, $this->pageError) = $this->cutSkinTag($sval, 'page_error'); 
			list($sval, $this->entry) = $this->cutSkinTag($sval, 'article_rep');
			list($sval, $this->pagingItem) = $this->cutSkinTag($sval, 'paging_rep');
			list($sval, $this->paging) = $this->cutSkinTag($sval, 'paging');
			list($sval, $this->archive) = $this->cutSkinTag($sval, 'archive_rep');
			list($sval, $this->search) = $this->cutSkinTag($sval, 'search');
			list($sval, $this->authorList) = $this->cutSkinTag($sval, 'author_rep');
			
			list($sval, $this->recentEntryItem) = $this->cutSkinTag($sval, 'rctps_rep');
			list($sval, $this->recentEntry) = $this->cutSkinTag($sval, 'rctps');
			list($sval, $this->recentCommentItem) = $this->cutSkinTag($sval, 'rctrp_rep');
			list($sval, $this->recentComment) = $this->cutSkinTag($sval, 'rctrp');
			list($sval, $this->recentTrackbackItem) = $this->cutSkinTag($sval, 'rcttb_rep');
			list($sval, $this->recentTrackback) = $this->cutSkinTag($sval, 'rcttb');
			
			list($sval, $this->link_rep) = $this->cutSkinTag($sval, 'link_rep');
			list($sval, $this->pageTitle) = $this->cutSkinTag($sval, 'page_title');
			$this->outter = $sval;
			$this->applyMicroformats();
			$this->dressTags = $__gDressTags;
			if($previewMode == false) $this->saveCache();
		}
	}
	
	function cutSkinTag($contents, $tag, $replace = null) {
		global $__gDressTags;
		if(!isset($__gDressTags)) $__gDressTags = array();
		if (is_null($replace)) {
			$replace = "[##_{$tag}_##]";
		}
		$tagSize = strlen($tag) + 4;
		$begin = strpos($contents, "<s_$tag>");
		if ($begin === false)
			return array($contents, NULL);
		$end = strpos($contents, "</s_$tag>", $begin + 4);
		if ($end === false)
			return array($contents, NULL);
		$inner = substr($contents, $begin + $tagSize, $end - $begin - $tagSize);
		$outter = substr($contents, 0, $begin) . $replace . substr($contents, $end + $tagSize + 1);
		if(!in_array($tag, $__gDressTags)) array_push($__gDressTags, $tag);
		return array($outter, $inner);
	}
	
	function cutSkinReplacer($contents, $tag, $replace = null) {
		global $__gDressTags;
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
		if(!in_array($tag, $__gDressTags)) array_push($__gDressTags, $tag);
		return array($outter, $inner);
	}

	function getDressTags($contents) {
		$matches = array();
		$result = array();
		preg_match_all('/\[##_(.+?)_##\]/',$contents,$matches);
		return array_unique($matches[1]);
	}

	function saveCache() {
		$skinCache = get_object_vars($this);
		$this->cache->contents = serialize($skinCache);
		return $this->cache->update();
	}
	
	function loadCache() {
		global $__gDressTags;
		if(!$this->cache->load()) return false;
		$skinCache = unserialize($this->cache->contents);
		foreach($skinCache as $key=>$value) {
			$this->$key = $value;
		}
		$__gDressTags = $this->dressTags;
		return true;
	}

	function purgeCache() {
		global $gCacheStorage;
		$this->cache->purge();
		$gCacheStorage->purge();
	}

	/**
	 * See: http://dev.textcube.org/wiki/MicroformatDeployment
	 */

	function applyMicroformats() {
		/* Spam과 관련된 것이므로 강제로 넣음 http://dev.textcube.org/ticket/75 */
		addAttribute( $this->trackback, 'a', 'href', '##_tb_rep_url_##', array( 'rel' => 'external' ) );
		addAttribute( $this->trackback, 'a', 'href', '##_tb_rep_url_##', array( 'rel' => 'nofollow' ) );

		$useMicroformat = Setting::getBlogSettingGlobal('useMicroformat',3);
		switch( $useMicroformat )
		{ 
			/* 1: none, 2: semantically sane, 3: insane but machine friendly */
			case 1:
			array_push( $this->microformatDebug, _text('Microformat-info: 스킨에 마이크로포맷 자동추가하지 않도록 설정되어 있습니다.') );
			return;
			case 2:
			array_push( $this->microformatDebug, _text('Microformat-info: 스킨에 웹표준에 맞는 마이크로포맷만 추가합니다.') );
			break;
			case 3:
			array_push( $this->microformatDebug, _text('Microformat-info: 스킨에 가능한 모든 마이크로포맷을 추가합니다.') );
			default:
		}

		/* hAtom, bookmark */
		$this->entryOriginal = $this->entry;
		//$this->noticeItemOriginal = $this->noticeItem;
		$this->entry = $this->applyMF2Entry( 'article', $this->entry );
		//$this->noticeItem = $this->applyMF2Entry( 'notice', $this->noticeItem );
		if( !$this->hentryExisted ) {
			$this->hentryExisted = preg_match( '/<(p|div)[^>]*class=[\'"][^\'"]*\bhentry\b[^\'"]*[\'"][^>]*>/sm', $this->entry );
		}

		/* hCard */
		$this->commentItem = $this->applyMF2Comment( "rp", $this->commentItem, _text("댓글") );
		$this->commentSubItem = $this->applyMF2Comment( "rp", $this->commentSubItem, _text("댓글의 댓글") );
		$this->guestItem = $this->applyMF2Comment( "guest", $this->guestItem, _text("방명록") );
		$this->guestSubItem = $this->applyMF2Comment( "guest", $this->guestSubItem, _text("방명록의 댓글") );
	}

	function getTopLevelContent($content)
	{
		$bareContent = preg_replace( '/[@%]/', '', $content );
		$bareContent = preg_replace( '@<(\w+\b[^>/]*/>)@sm', '', $bareContent );
		$bareContent = preg_replace( '@<(\w+\b[^>]*>)@sm', '@', $bareContent );
		$bareContent = preg_replace( '@</(\w+\b[^>]*>)@sm', '%', $bareContent );
		$bareContent2 = '';
		while( $bareContent != $bareContent2 ) {
			$bareContent2 = $bareContent;
			$bareContent = preg_replace( '/@[^@%]*%/sm', '', $bareContent );
		}
		$bareContent = preg_replace( '/[@%]/', '', $bareContent );
		return $bareContent;
	}

	function applyMF2Entry( $type, $content ) {
		/* This function contains heavy regular expressions, but this function is called once and stored in cache by skin setup logic */
		$bareContent = $this->getTopLevelContent($content);
		$content = preg_replace( "/<((br|hr|img)[^>]*)>/sm", "{{{\\1}}}", $content );

		/* hAtom:entry-title */
		$content = addAttributeCore( $content,
					'@(.*?)(<(a|span|textarea|td|h1|h2|h3|strong|cite|font)[^>]*>[^<>]*?\[##_article_rep_title_##\].*?</\3>)(.*)@sm',
					array( 'class' => 'entry-title' ), array(1,2,4), 1 );
		if( preg_match( '@<(a|span|textarea|td|h1|h2|h3|strong|cite|font)[^>]*?class=[\'"][^\'"]*entry-title[^\'"]*[\'"][^>]*>@sm', $content ) ) {
			array_push( $this->microformatDebug, _text('Microformat-info: 제목에 hAtom용 title을 추가합니다.') );
		} else {
			array_push( $this->microformatDebug, _text('Microformat-warn: 제목에 hAtom용 title을 추가하지 못했습니다.') );
		}


		/* hAtom:entry-content */
		$content = addAttributeCore( $content,
					'@(.*?)(<(div|td|span|p)[^>]*>[^<>]*?\[##_article_rep_desc_##\].*?</\3>)(.*)@sm',
					array( 'class' => 'entry-content' ), array(1,2,4), 1 );
		if( strstr( $bareContent , '[##_article_rep_desc_##]' ) !== false ) {
			$content = str_replace( "[##_article_rep_desc_##]", "<div class=\"entry-content\">[##_article_rep_desc_##]</div>",$content );
			array_push( $this->microformatDebug, _text('Microformat-info: 본문을 감싸고 있는 태그가 없어 div를 삽입한 뒤 hAtom용 entry-content를 추가합니다.') );
		} else {
			if( preg_match( '@<(div|td|span|p)[^>]*?class=[\'"][^\'"]*entry-content[^\'"]*[\'"][^>]*>@sm', $content ) ) {
				array_push( $this->microformatDebug, _text('Microformat-info: hAtom용 content를 추가합니다.') );
			} else {
				array_push( $this->microformatDebug, _text('Microformat-warn: hAtom용 content를 추가하지 못했습니다.') );
			}
		}

		/* hAtom:updated, published */
		if(Setting::getBlogSettingGlobal('useMicroformat',3)>2) {
			/* Adding published, updated date */
			$content = preg_replace( 
					'@(<(div|td|span|p)[^>]*>[^<>]*?\[##_article_rep_desc_##\].*?</\2>)@sm',
					"\\1
					<div style=\"display:none\">
					<abbr class=\"updated\" title=\"[##_article_rep_microformat_updated_##]\">[##_article_rep_date_##]</abbr>
					<abbr class=\"published\" title=\"[##_article_rep_microformat_published_##]\">[##_article_rep_date_##]</abbr>
					</div>", 
					$content );
			if( preg_match( '@<abbr[^>]*?class=[\'"][^\'"]*\bupdated\b[^\'"]*[\'"][^>]*>@sm', $content ) ) {
				array_push( $this->microformatDebug, _text('Microformat-info: hAtom용 발행일(published,updated)을 보이지 않게 추가하였습니다.') );
			} else {
				array_push( $this->microformatDebug, _text('Microformat-warn: hAtom용 발행일을 추가하지 못하였습니다.') );
			}
		} else {
			array_push( $this->microformatDebug, _text('Microformat-info: 의미상 어긋나는 사용인 hAtom용 발행일(published,updated)을 추가하지 않았습니다.') );
		}

		/* hAtom:author should be a complete inner text(without other text such as 'By', 'From') of span,cite,label,li. */
		if( !preg_match( '@<(\w+)\b[^>]*?class=[\'"][^\'"]*author[^\'"]*[\'"][^>]*>@sm', $content ) ) {
			if( preg_match( '@<(span|cite|label|li)[^>]*>([^<>]*)\[##_article_rep_author_##\]([^<>]*)</\1>@sm',$content,$match) ) {
				/* If there are garbage texts around author's name..., embrace author's name with a span */
				if( !preg_match( '/^\s*$/', $match[2] ) || !preg_match( '/^\s*$/', $match[3] ) ) {
					$content = str_replace( "[##_article_rep_author_##]", "<span>[##_article_rep_author_##]</span>", $content );
					array_push( $this->microformatDebug, _text('Microformat-info: 작성자 주위로 공백외의 문자가 있어 <span>으로 한번 더 감쌉니다.') );
				}
			} else {
				array_push( $this->microformatDebug, _text('Microformat-info: 작성자가 출력되지 않는 스킨입니다. 작성자를 보이지 않게 추가하였습니다.') );
				$content = preg_replace( 
					'@(<(div|td|span|p)[^>]*>[^<>]*?\[##_article_rep_desc_##\].*?</\2>)@sm',
					"\\1
					<span style=\"display:none\">[##_article_rep_author_##]</span>",
					$content );
			}
			$content = addAttributeCore( $content,
						'@(.*?)(<(span|cite|label|li|div)[^>]*>\s*\[##_article_rep_author_##\]\s*</\3>)(.*)@sm',
						array( 'class' => 'author' ), array(1,2,4), 1 );
			if( preg_match( '@<(span|cite|label|li|div)[^>]*?class=[\'"][^\'"]*\bauthor\b[^\'"]*[\'"][^>]*>@sm', $content ) ) {
				array_push( $this->microformatDebug, _text('Microformat-info: 작성자에 hAtom용 class="author"를 추가합니다') );
			} else {
				array_push( $this->microformatDebug, _text('Microformat-warn: 작성자에 hAtom용 class="author"를 추가하지 못하였습니다') );
			}
		} else {
			array_push( $this->microformatDebug, _text('Microformat-info: class="author"를 사용한 스킨입니다. hAtom용 author는 삽입하지 않았습니다. 주의: 스킨에 사용된 author의 용도가 마이크로포맷과 다를 수 있습니다.') );
		}

		/* hAtom:hEntry is a unique division in a blog entry*/
		if( !preg_match( '/<(p|div)[^>]*class=[\'"][^\'"]*\bhentry\b[^\'"]*[\'"][^>]*>/sm', $content ) )
		{
			$content = "<div class=\"hentry\">\r\n{$content}\r\n</div>";
			array_push( $this->microformatDebug, _text('Microformat-info: 블로그 글영역 전체를 hAtom용 entry로 간주합니다. 적절한 class="hentry" 삽입이 필요할 수 있습니다.') );
		} else {
			array_push( $this->microformatDebug, _text('Microformat-info: 스킨에 class="hentry"가 있으므로, hAtom용 entry는 삽입하지 않았습니다.') );
		}

		/* bookmark to A link */
		addAttribute( $content, 'a', 'href', '##_article_rep_link_##', 
			array( 'rel' => 'bookmark', 'title' => "[##_article_rep_title_##]" ) );
		if( preg_match( '@<a\b[^>]*?rel=[\'"][^\'"]*bookmark[^\'"]*[\'"][^>]*>@sm', $content ) ) {
			array_push( $this->microformatDebug, _text('Microformat-info: 제목에 bookmark를 추가합니다') );
		} else {
			if(Setting::getBlogSettingGlobal('useMicroformat',3)>2) {
				$content = str_replace( "[##_article_rep_desc_##]", "<a style=\"display:none\" href=\"[##_article_rep_link_##]\" rel=\"bookmark\" class=\"entry-title\" title=\"[##_article_rep_title_##]\">[##_article_rep_title_##]</a>\r\n[##_article_rep_desc_##]",$content );
			}
			array_push( $this->microformatDebug, _text('Microformat-info: 링크가 걸린 제목이 없어 보이지 않는 링크를 추가한 뒤 rel="bookmark"와 hAtom용 title을 추가하였습니다') );
		}

		$this->hentryExisted = true;
		$content = preg_replace( "/{{{([^}]+)}}}/sm", '<\1>', $content );
		return $content;
	}

	function applyMF2Comment( $type, $commentItem, $debugType ) {
		$type .= "_rep";
		if( !preg_match( '/<(p|div|li|table|tr|td|span|cite|strong)[^>]*class=[\'"][^\'"]*\bvcard\b[^\'"]*[\'"][^>]*>/sm', $commentItem ) )
		{
			$commentItem = addAttributeCore( $commentItem,
						'@(.*?)(<(p|div|li|td|span|cite|strong)[^>]*>[^<>]*?\[##_'.$type.'_name_##\].*?</\3>)(.*)@sm',
						array( 'class' => 'vcard' ), array(1,2,4), 1 );
			if( !preg_match( '@<(p|div|li|td|span|cite|strong)[^>]*class=[\'"][^\'"]*\bvcard\b[^\'"]*[\'"][^>]*>@sm', $commentItem ) ) {
				$commentItem = str_replace( "[##_{$type}_name_##]", "<span class=\"vcard\">[##_{$type}_name_##]</span>",$commentItem );
				array_push( $this->microformatDebug, _textf('Microformat-info: %1 작성자를 감싸고 있는 태그가 없어 span으로 감싼 뒤 class="vcard"를 추가합니다.', $debugType) );
			} else {
				array_push( $this->microformatDebug, _textf('Microformat-info: %1 영역에 class="vcard"를 삽입합니다.', $debugType) );
			}
		} else {
			array_push( $this->microformatDebug, _textf('Microformat-info: %1 영역에 class="vcard"가 있으므로, vcard는 삽입하지 않았습니다.', $debugType) );
		}
		return $commentItem;
	}

}

class KeylogSkin {
	var $outter;
	var $skin;
	var $keylog;
	var $keylogItem;

	function KeylogSkin($filename) {
		global $service, $serviceURL;
		if (!$sval = file_get_contents($filename))
			Respond::ErrorPage("KeywordSkin");
		$origPath = $serviceURL . substr($filename,strlen(ROOT));
		$origPath = substr($origPath, 0, 0 - strlen(Path::getBaseName($origPath)));
		$sval = str_replace('./', $origPath, $sval);
		replaceSkinTag($sval, 'html');
		replaceSkinTag($sval, 'head');
		replaceSkinTag($sval, 'body');
		list($sval, $this->keylogItem) = $this->cutSkinTag($sval, 'blog_rep');
		list($sval, $this->keylog) = $this->cutSkinTag($sval, 'blog');
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

function dressStaticElements(& $view) {
	global $blogid, $blog, $defaultURL, $blogURL, $service, $serviceURL;

	$writer = User::getBlogOwnerName($blogid);

	dress('title', htmlspecialchars($blog['title']), $view);
	dress('blogger', htmlspecialchars($writer), $view);
	dress('desc', htmlspecialchars($blog['description']), $view);
	if (!empty($blog['logo']))
		dress('image', "{$serviceURL}/attach/$blogid/{$blog['logo']}", $view);
	else
		dress('image', "{$serviceURL}/resources/image/spacer.gif", $view);
	dress('blog_link', "$blogURL/", $view);
	dress('keylog_link', "$blogURL/keylog", $view);
	dress('localog_link', "$blogURL/location", $view);
	dress('taglog_link', "$blogURL/tag", $view);
	dress('guestbook_link', "$blogURL/guestbook", $view);
	
	dress('rss_url', "$defaultURL/rss", $view);
	dress('response_rss_url', "$defaultURL/rss/response", $view);
	dress('comment_rss_url', "$defaultURL/rss/comment", $view);
	dress('trackback_rss_url', "$defaultURL/rss/trackback", $view);
	
	dress('atom_url', "$defaultURL/atom", $view);
	dress('response_atom_url', "$defaultURL/atom/response", $view);
	dress('comment_atom_url', "$defaultURL/atom/comment", $view);
	dress('trackback_atom_url', "$defaultURL/atom/trackback", $view);
	
	dress('owner_url', "$blogURL/owner", $view);
	dress('textcube_name', TEXTCUBE_NAME, $view);
	dress('textcube_version', TEXTCUBE_VERSION, $view);
	dress('tattertools_name', TEXTCUBE_NAME, $view); // For skin legacy.
	dress('tattertools_version', TEXTCUBE_VERSION, $view);
}

function removeAllTags($contents) {
	handleTags($contents);
	$contents = preg_replace('/\[#M_[^|]*\|[^|]*\|/Us', '', str_replace('_M#]', '', preg_replace('/\[##_.+_##\]/Us', '', $contents)));
	$contents = preg_replace('@<(s_[0-9a-zA-Z_]+)>.*?</\1>@s', '', $contents);
	return $contents;	
}

function replaceSkinTag( & $contents, $tag) {
	$pattern[] = '/(<'.$tag.'.*>)\r?\n/Ui';
	$pattern[] = '/<\/'.$tag.'>/Ui';

	$replacement[] = '$1'.CRLF.'[##_SKIN_'.$tag.'_start_##]';
	$replacement[] = '[##_SKIN_'.$tag.'_end_##]$0';

	$contents = preg_replace($pattern, $replacement, $contents);
}

function insertGeneratorVersion(&$contents) {
	$pattern = '/(<head>)/Ui';
	$replacement = '$1'.CRLF.'<meta name="generator" content="'.TEXTCUBE_NAME.' '.TEXTCUBE_VERSION.'" />';

	$contents = preg_replace($pattern, $replacement, $contents);
}

function setTempTag($name) {
	return "[#####_#####_#####_{$name}_#####_#####_#####]";
}

function revertTempTags($content) {
	global $contentContainer;
	
	if(is_array($contentContainer)) {
		$keys = array_keys($contentContainer);
		for ($i=0; $i<count($keys); $i++) {
			$content = str_replace("[#####_#####_#####_{$keys[$i]}_#####_#####_#####]", $contentContainer[$keys[$i]], $content);
//			unset($contentContainer[$keys[$i]]);
		}
	}
	return $content;
}

/* You need 3 parenthesis' for match 1,2,3 */
function addAttributeCore(& $string, $regex, $addings, $mi = null, $count = 10000) {
	if( $count < 1 ) { return $string; }
	if( !preg_match( $regex, $string, $match ) ) {
		return $string;
	}
	if( $mi === null ) { $mi = array( 1,2,3 ); }
	foreach( $addings as $add_attr => $add_value ) {
		$regex_attr = "/([^>]*)$add_attr=[\"']([^\"']*)[\"'](.*)/sm";
		/* Does the tag have already add_attr attribute? */
		if( preg_match( $regex_attr, $match[2], $attr_match ) ) {
			/* Does not the attribute have add_value value? */
			if( !preg_match( "/\\b$add_value\\b/i", $attr_match[2] ) ) {
				if( !empty( $attr_match[2] ) ) {
					$attr_match[2] .= ' ';
				}
				$match[$mi[1]] = "{$attr_match[1]}$add_attr=\"{$attr_match[2]}$add_value\"{$attr_match[3]}";
			} else {
			}
		} else {
			$match[$mi[1]] = preg_replace( "/(.*?)(\\/?>)/", "\\1 $add_attr=\"$add_value\"\\2", $match[$mi[1]], 1 );
		}
	}
	return $match[$mi[0]].$match[$mi[1]].addAttributeCore($match[$mi[2]],$regex,$addings,$mi, $count-1);
}

function addAttribute(& $skin, $tag, $cond_attr, $cond_value, $addings, $count = 10000) {
	if( !empty($cond_attr ) && !empty($cond_value) ) {
		$needle = "/(.*?)(<$tag\\s+[^>]*{$cond_attr}=[\"'][^\"'>]*{$cond_value}[^\"'>]*[\"'][^>]*>)(.*)/s";
	} else {
		$needle = "/(.*?)(<{$tag}[^>]*>)(.*)/s";
	}
	$skin = addAttributeCore( $skin, $needle, $addings, array(1,2,3), $count );
}

?>
