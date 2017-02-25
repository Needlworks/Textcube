<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function printHtmlHeader($title = '') {
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $title;?></title>
</head>
<body>
<?php
}

function printHtmlFooter() {
?>
</body>
</html>
<?php
}

function dress($tag, $value, & $contents, $useCache = false, $forcePatch = false) {
	global $__gDressTags;
	if($useCache == true) {
		if(strpos($tag, 'sidebar_') !== false ||
			strpos($tag, 'coverpage_') !== false ||
			in_array($tag, $__gDressTags) ) {
			$contents = str_replace("[##_{$tag}_##]", $value, $contents);
			return true;
		} else {
			return false;
		}
	} else {
		if($forcePatch == true) {
			$contents = str_replace("[##_{$tag}_##]", $value, $contents);
			return true;
		} else if (preg_match("@\\[##_{$tag}_##\\]@iU", $contents)) {
			$contents = str_replace("[##_{$tag}_##]", $value, $contents);
			return true;
		} else return false;
	}
}

function dressInsertBefore($tag, $value, & $contents, $useCache = false, $forcePatch = false) {
	global $__gDressTags;
	if($useCache == true) {
		if(strpos($tag, 'sidebar_') !== false ||
			strpos($tag, 'coverpage_') !== false ||
			in_array($tag, $__gDressTags) ) {
			$tempContents = preg_split("@\\[##_{$tag}_##\\]@iU", $contents, 2);
			$contents = $tempContents[0].$value.'[##_'.$tag.'_##]'.$tempContents[1];
			return true;
		} else {
			return false;
		}
	} else if (preg_match("@\\[##_{$tag}_##\\]@iU", $contents)) {
		$tempContents = preg_split("@\\[##_{$tag}_##\\]@iU", $contents, 2);
		$contents = $tempContents[0].$value.'[##_'.$tag.'_##]'.$tempContents[1];
		return true;
	} else {
		return false;
	}
}

function getScriptsOnHead($paging, $entryIds = null) {
	$context = Model_Context::getInstance();
	ob_start();
?>
	<script type="text/javascript" src="<?php echo (doesHaveOwnership() ? $context->getProperty('service.path').'/resources' : $context->getProperty('service.resourcepath'));?>/script/jquery/jquery-<?php echo JQUERY_VERSION;?>.js"></script>
	<script type="text/javascript" src="<?php echo (doesHaveOwnership() ? $context->getProperty('service.path').'/resources' : $context->getProperty('service.resourcepath'));?>/script/jquery/jquery.bpopup-<?php echo JQUERY_BPOPUP_VERSION;?>.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>

	<script type="text/javascript" src="<?php echo $context->getProperty('service.resourcepath');?>/script/EAF4.js"></script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.resourcepath');?>/script/common3.js"></script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.resourcepath');?>/script/gallery.js" ></script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.resourcepath');?>/script/flash.js" ></script>
	<script type="text/javascript">
	//<![CDATA[
		var servicePath = "<?php echo $context->getProperty('service.path');?>";
		var serviceURL  = "<?php echo $context->getProperty('uri.service');?>";
		var blogURL = "<?php echo $context->getProperty('uri.blog');?>";
		var prevURL = "<?php echo isset($paging['prev']) ? escapeJSInCData("{$paging['url']}{$paging['prefix']}{$paging['prev']}{$paging['postfix']}") : '';?>";
		var nextURL = "<?php echo isset($paging['next']) ? escapeJSInCData("{$paging['url']}{$paging['prefix']}{$paging['next']}{$paging['postfix']}") : '';?>";
		var commentKey = "<?php echo md5(filemtime(ROOT . '/config.php'));?>";
		var doesHaveOwnership = <?php echo doesHaveOwnership() ? 'true' : 'false'; ?>;
		var isReaderEnabled = <?php echo ($context->getProperty('service.reader') ? 'true' : 'false'); ?>;
		var displayMode = "<?php echo $context->getProperty('blog.displaymode','desktop');?>";
		var workMode = "<?php echo $context->getProperty('blog.workmode','enhanced');?>";
		var cookie_prefix = "<?php echo $context->getProperty('service.cookie_prefix','');?>";
<?php
	if (!is_null($entryIds)) {
?>
		var entryIds = [<?php echo implode(',',$entryIds);?>];
<?php
	}
?>
		var messages = {
			"trackbackUrlCopied": "<?php echo _text('엮인글 주소가 복사되었습니다.');?>",
			"operationFailed": "<?php echo _text('실패했습니다.');?>",
			"confirmTrackbackDelete": "<?php echo _text('선택된 글걸기를 삭제합니다. 계속 하시겠습니까?');?>",
			"confirmEntryDelete": "<?php echo _text('이 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?');?>",
			"onSaving": "<?php echo _text('저장하고 있습니다');?>"
		}
	//]]>
	</script>
<?php
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getUpperView() {
	$context = Model_Context::getInstance();
	ob_start();
?>
	<!--
		<?php echo TEXTCUBE_NAME." ".TEXTCUBE_VERSION.CRLF;?>

		Homepage: <?php echo TEXTCUBE_HOMEPAGE.CRLF;?>
		<?php echo TEXTCUBE_COPYRIGHT.CRLF;?>
	-->
<?php
	if (doesHaveOwnership()) {
?>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.resourcepath');?>/script/owner.js" ></script>
<?php
	}
?>
	<script type="text/javascript">
		//<![CDATA[
			document.onkeydown = processShortcut;
		//]]>
	</script>
<?php
	if($context->getProperty('service.flashclipboardpoter') == true) {
?>
<div style="position:absolute;top:0;left:0; background-color:transparent;background-image:none">
<script type="text/javascript">
//<![CDATA[
	AC_FL_RunContent(
		'codebase','http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0',
		'width','1',
		'height','1',
		'id','clipboardPoter',
		'src','<?php echo $context->getProperty('service.path');?>/resources/script/clipboardPoter/clipboardPoter',
		'wmode','transparent',
		'name','clipboardPoter',
		'allowscriptaccess','sameDomain',
		'pluginspage','http://www.macromedia.com/go/getflashplayer',
		'movie','<?php echo $context->getProperty('service.path');?>/resources/script/clipboardPoter/clipboardPoter',
		'flashvars', 'callback=onClipBoard'
	);
	window.clipboardPoter = document.getElementById("clipboardPoter");
//]]>
</script>
</div>
<?php
	}
?>
<div id="tcDialog" style="display:none;"></div>
<?php
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getLowerView() {
	ob_start();
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getScriptsOnFoot() {
	$context = Model_Context::getInstance();
	ob_start();
    if (($context->getProperty('service.reader') != false) && (gmmktime() - Setting::getServiceSetting('lastFeedUpdate', 0, true) > 180)) {
?>
	<script type="text/javascript">
		//<![CDATA[
			updateFeed();
		//]]>
	</script>
<?php
		$view = ob_get_contents();
		ob_end_clean();
		return $view;
	} else return '';
}

function getTrackbacksView($entry, $skin, $accepttrackback) {
	global $suri, $defaultURL, $skinSetting, $blogURL, $service, $blog;
	requireModel('blog.response.remote');
	requireLibrary('blog.skin');

	$trackbacksContainer = $skin->trackbackContainer;
	$trackbacksView = '';
	$trackbacks = getTrackbacks($entry['id']);
	foreach ($trackbacks as $trackback) {
		$trackbackView = $skin->trackback;
		dress('tb_rep_title', htmlspecialchars(fireEvent('ViewTrackbackTitle', $trackback['subject'], array($trackback['id'], $trackback['url']))), $trackbackView);
		dress('tb_rep_site', htmlspecialchars($trackback['site']), $trackbackView);
		dress('tb_rep_url', htmlspecialchars($trackback['url']), $trackbackView);
		dress('tb_rep_desc', htmlspecialchars($trackback['excerpt']), $trackbackView);
		dress('tb_rep_onclick_delete', "deleteTrackback({$trackback['id']}, {$entry['id']})", $trackbackView);
		dress('tb_rep_date', fireEvent('ViewTrackbackDate', Timestamp::format5($trackback['written']), $trackback['written']), $trackbackView);
		if (dress('tb_rep_id', 'trackback' . $trackback['id'] , $trackbackView) == false) {
			$trackbackView = "<a id=\"trackback{$trackback['id']}\"></a>" . $trackbackView;
		}
		$trackbacksView .= $trackbackView;
	}

	if (count($trackbacks) > 0) {
		dress('tb_rep', $trackbacksView, $trackbacksContainer);
	} else {
		$trackbacksContainer = '';
	}

	if ($skinSetting['expandTrackback'] == 1 || (($suri['url'] != $blogURL.'/index.php' && $suri['url'] != $service['path'].'/index.php') && ($suri['directive'] == '/' || $suri['directive'] == '/entry') && $suri['value'] != '')) {
		$style = 'block';
	} else {
		$style = 'none';
	}
	$trackbacksView = "<div id=\"entry{$entry['id']}Trackback\" style=\"display:$style\">" . str_replace('[##_tb_container_##]', $trackbacksContainer, $skin->trackbacks) . '</div>';


	if(Setting::getBlogSettingGlobal('acceptTrackbacks',1) && $accepttrackback) {
		// Blocked. (Too many encoding issues with various trackback sender.)
		//$trackbackAddress = $defaultURL."/trackback/".($blog['useSloganOnPost'] ? $entry['slogan'] : $entry['id']);
		$trackbackAddress = $defaultURL."/trackback/".$entry['id'];
		dress('tb_address', "<span onclick=\"copyUrl('$trackbackAddress', this)\">$trackbackAddress</span>", $trackbacksView);
	}
	else
		dress('tb_address', _t('이 글에는 트랙백을 보낼 수 없습니다'), $trackbacksView);

	return $trackbacksView;
}

function getCommentView($entry, $skin, $inputBlock = true, $page = 1, $count = null, $listBlock = true) {
	global $contentContainer, $skinSetting, $skin;
	static $dressCommentBlock = false;
	$context = Model_Context::getInstance();
	if(is_null($count)) {
		if($context->getProperty('skin.commentsOnEntry')) {
			$count = $context->getProperty('skin.commentsOnEntry');
		} else {
			$count = 15;
		}
	}
	if(!isset($entry)) $entry['id'] = 0;
	$blogid = getBlogId();
	requireModel("common.setting");
	requireModel("blog.entry");
	requireModel("blog.comment");
	requireLibrary('blog.skin');
	$authorized = doesHaveOwnership();
	$useAjaxBlock = $context->getProperty('blog.useAjaxComment',true);
	$useMicroformat = $context->getProperty('blog.useMicroformat',3);
	$fn = '';
	$fn_nickname = '';
	if( $useMicroformat > 1 ) {
		$fn = 'class="fn url nickname" ';
		$fn_nickname = 'class="fn nickname" ';
	}
	if ($entry['id'] > 0) {
		$prefix1 = 'rp';
		$isComment = true;
	} else {
		$prefix1 = 'guest';
		$isComment = false;
	}
	$commentView = ($isComment ? $skin->comment : $skin->guest);
	$commentItemsView = '';
	if ($listBlock === true) {
		if ($isComment == false) {
			global $comments;
			if(!isset($comments)) {
				list($comments, $paging) = getCommentsWithPagingForGuestbook($blogid, $context->getProperty('suri.page'), $skinSetting['commentsOnGuestbook']);
			}
			foreach ($comments as $key => $value) {
				if ($value['secret'] == 1) {
					if (!$authorized) {
						if( !doesHaveOpenIDPriv($value) ) {
							$comments[$key]['name'] = _text('비밀방문자');
							$comments[$key]['homepage'] = '';
							$comments[$key]['comment'] = _text('관리자만 볼 수 있는 방명록입니다.');
						} else {
							$comments[$key]['name'] = _text('비밀방문자') .' '. $comments[$key]['name'];
						}
					}
				}
			}
		} else {
			if($useAjaxBlock) {
				list($comments, $paging) = getCommentsWithPagingByEntryId($blogid, $entry['id'], $page, $count,'loadComment','('.$entry['id'].',',',true,true);return false;',null, $context->getProperty('skin.sortCommentsBy','ASC'));
			} else {
				$comments = getComments($entry['id'],$context->getProperty('skin.sortCommentsBy','ASC'));
			}
		}
		if(empty($skin->dressCommentBlock)) {
			if( $dressCommentBlock ) {
				if($isComment) $skin->commentGuest = $dressCommentBlock;
				else $skin->guestGuest = $dressCommentBlock;
			} else {
				if($isComment) $dressCommentBlock = $skin->commentGuest = addOpenIDPannel( $skin->commentGuest, 'rp' );
				else $dressCommentBlock = $skin->guestGuest = addOpenIDPannel( $skin->guestGuest, 'guest' );
			}
			$skin->dressCommentBlock = true;
		}
		/// Dressing comments
		foreach ($comments as $commentItem) {
			$commentItemView = ($isComment ? $skin->commentItem : $skin->guestItem);
			$commentSubItemsView = '';
			$subComments = getCommentComments($commentItem['id'],$commentItem);
			foreach ($subComments as $commentSubItem) {
				$commentSubItemView = ($isComment ? $skin->commentSubItem : $skin->guestSubItem);

				$commentSubItem['name'] = htmlspecialchars($commentSubItem['name']);
				$commentSubItem['comment'] = htmlspecialchars($commentSubItem['comment']);

				$rp_class = $prefix1 . '_general';
				if ($blogid == $commentSubItem['replier'])
					$rp_class = $prefix1 . '_admin';
				else if ($commentSubItem['secret'] == 1) {
					$rp_class = $prefix1 . '_secret';
					if ($authorized) {
						$commentSubItem['comment'] = '<span class="hiddenCommentTag_content">' . _text('[비밀댓글]') . '</span> ' . $commentSubItem['comment'];
					} else {
						$rp_class .= ' hiddenComment';
						$commentSubItem['name'] = '<span class="hiddenCommentTag_name">' . _text('비밀방문자') . '</span>'.(doesHaveOpenIDPriv($commentSubItem)?' '.$commentSubItem['name']:'');
					}
				}
				dress($prefix1 . '_rep_class', $rp_class, $commentSubItemView);

				if (dress($prefix1 . '_rep_id',($entry['id'] == 0 ? 'guestbook' : 'comment') . $commentSubItem['id'], $commentSubItemView) == false) {
					$commentSubItemView = "<a id=\"comment{$commentSubItem['id']}\"></a>" . $commentSubItemView;
				}
				if (empty($commentSubItem['homepage']) ||
					(($commentSubItem['secret'] == 1) && !doesHaveOwnership())) {
					dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), "<span $fn_nickname>".$commentSubItem['name']."</span>", $commentSubItem), $commentSubItemView);
				} else {
					dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), '<a '.$fn.'rel="external nofollow" href="' . htmlspecialchars(addProtocolSense($commentSubItem['homepage'])) . '" onclick="return openLinkInNewWindow(this)">' . $commentSubItem['name'] . '</a>', $commentSubItem), $commentSubItemView);
				}
				$contentContainer["{$prefix1}_{$commentSubItem['id']}"] = fireEvent(($isComment ? 'ViewCommentContent' : 'ViewGuestCommentContent'), nl2br(addLinkSense($commentSubItem['comment'], ' onclick="return openLinkInNewWindow(this)"')), $commentSubItem);
				dress($prefix1 . '_rep_desc', setTempTag("{$prefix1}_{$commentSubItem['id']}"), $commentSubItemView);
				dress($prefix1 . '_rep_date', fireEvent(($isComment ? 'ViewCommentDate' : 'ViewGuestCommentDate'), Timestamp::format5($commentSubItem['written']), $commentSubItem['written']), $commentSubItemView);
				dress($prefix1 . '_rep_link',$context->getProperty('uri.blog')."/".($entry['id'] == 0 ? "guestbook/{$commentItem['id']}#guestbook{$commentSubItem['id']}" : ($context->getProperty('blog.useSloganOnPost') ? "entry/".URL::encode($entry['slogan'],$context->getProperty('service.useEncodedURL')) : $entry['id'])."#comment{$commentSubItem['id']}"), $commentSubItemView);
				dress($prefix1 . '_rep_onclick_delete', "deleteComment({$commentSubItem['id']}); return false;", $commentSubItemView);

				$commentSubItemsView .= $commentSubItemView;
			}
			$commentSubContainer = ($isComment ? $skin->commentSubContainer : $skin->guestSubContainer);
			dress(($isComment ? 'rp2_rep' : 'guest_reply_rep'), $commentSubItemsView, $commentSubContainer);
			if (count($subComments) > 0) {
				dress(($isComment ? 'rp2_container' : 'guest_reply_container'), $commentSubContainer, $commentItemView);
			}

			$commentItem['name'] = htmlspecialchars($commentItem['name']);
			$commentItem['comment'] = htmlspecialchars($commentItem['comment']);

			$rp_class = $prefix1 . '_general';
			if ($blogid == $commentItem['replier'])
				$rp_class = $prefix1 . '_admin';
			else if ($commentItem['secret'] == 1) {
				$rp_class = $prefix1 . '_secret';
				if ($authorized) {
					$commentItem['comment'] = '<span class="hiddenCommentTag_content">' . _text('[비밀댓글]') . '</span> ' . $commentItem['comment'];
				} else {
					$rp_class .= ' hiddenComment';
					$commentItem['name'] = '<span class="hiddenCommentTag_name">' . _text('비밀방문자') . '</span>'.(doesHaveOpenIDPriv($commentItem)?' '.$commentItem['name']:'');
				}
			}
			dress($prefix1 . '_rep_class', $rp_class, $commentItemView);
			if (dress($prefix1 . '_rep_id', ($entry['id'] == 0 ? 'guestbook' : 'comment') . $commentItem['id'], $commentItemView) == false) {
				$commentItemView = "<a id=\"comment{$commentItem['id']}\"></a>" . $commentItemView;
			}
			if (empty($commentItem['homepage']) ||
				(($commentItem['secret'] == 1) && !doesHaveOwnership())) {
				dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), "<span $fn_nickname>".$commentItem['name']."</span>", $commentItem), $commentItemView);
			} else {
				dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), '<a '.$fn.'rel="external nofollow" href="' . htmlspecialchars(addProtocolSense($commentItem['homepage'])) . '" onclick="return openLinkInNewWindow(this)">' . $commentItem['name'] . '</a>', $commentItem), $commentItemView);
			}
			$contentContainer["{$prefix1}_{$commentItem['id']}"] = fireEvent(($isComment ? 'ViewCommentContent' : 'ViewGuestCommentContent'), nl2br(addLinkSense($commentItem['comment'], ' onclick="return openLinkInNewWindow(this)"')), $commentItem);
			dress($prefix1 . '_rep_desc', setTempTag("{$prefix1}_{$commentItem['id']}"), $commentItemView);
			dress($prefix1 . '_rep_date', fireEvent(($isComment ? 'ViewCommentDate' : 'ViewGuestCommentDate'), Timestamp::format5($commentItem['written']), $commentItem['written']), $commentItemView);
			if ((!$context->getProperty('blog.acceptComments',true))||($prefix1 == 'guest' && $authorized != true && $context->getProperty('blog.allowWriteDblCommentOnGuestbook') == 0)) {
				$doubleCommentPermissionScript = 'alert(\'' . _text('댓글을 사용할 수 없습니다.') . '\'); return false;';
			} else {
				$doubleCommentPermissionScript = '';
			}
			dress($prefix1 . '_rep_onclick_reply', $doubleCommentPermissionScript . "commentComment({$commentItem['id']}); return false", $commentItemView);
			dress($prefix1 . '_rep_onclick_delete', "deleteComment({$commentItem['id']});return false", $commentItemView);
			dress($prefix1 . '_rep_link',
				$context->getProperty('uri.blog')."/".($entry['id'] == 0 ? "guestbook/{$commentItem['id']}#guestbook{$commentItem['id']}" :
				($context->getProperty('blog.useSloganOnPost') ? "entry/".URL::encode($entry['slogan'],$context->getProperty('service.useEncodedURL')) : $entry['id'])."?commentId=".$commentItem['id']."#comment{$commentItem['id']}"), $commentItemView);

			$commentItemsView .= $commentItemView;
		}
		/// Merging comments with its paging links.
		$commentContainer = ($isComment ? $skin->commentContainer : $skin->guestContainer);
		dress(($isComment ? 'rp_rep' : 'guest_rep'), $commentItemsView, $commentContainer);
		if (count($comments) > 0) {
			if($isComment && $useAjaxBlock) {
				$pagingView = Paging::getPagingView($paging, $skin->paging, $skin->pagingItem, false, 'onclick');
			} else $pagingView = '';
			dress($prefix1 . '_container', "<div id=\"entry".$entry['id']."CommentList\">".$commentContainer.$pagingView."</div>", $commentView);
		}
	} else {
		dress($prefix1 . '_container', '', $commentView);
	}
	/// Comment write block
	if($inputBlock == true) {
		if(!empty($entry['acceptcomment'])) {
			$acceptcomment = $entry['acceptcomment'];
		} else {
			$pool = DBModel::getInstance();
			$pool->reset('Entries');
			$pool->setQualifier('blogid','equals',$blogid);
			$pool->setQualifier('id','equals',$entry['id']);
			$pool->setQualifier('draft','equals',0);
			$acceptcomment = $pool->getCell('acceptcomment');
		}
		$useForm = false;
		$openid_identity = Acl::getIdentity('openid');
		if ($isComment) {
			if (!($skin->commentForm == '')) {
				$commentRrevView = $commentView;	/// Comment Lists.
				$commentView = $skin->commentForm;	/// Comment write block.
				$useForm = true;
			}
		} else {
			if (!($skin->guestForm == '')) {
				$commentRrevView = $commentView;
				$commentView = $skin->guestForm;
				$useForm = true;
			}
		}

		$default_guestname = '';
		$default_homepage = '';
		if (doesHaveOwnership() || ($isComment && $acceptcomment == 1 && Setting::getBlogSettingGlobal('acceptComments',1)) || ($isComment == false) || ($useForm == false)) {
			$commentMemberView = ($isComment ? $skin->commentMember : $skin->guestMember);
			if (!doesHaveMembership()) {
				$commentGuestView = ($isComment ? $skin->commentGuest : $skin->guestGuest);
				dress($prefix1 . '_input_name', 'name', $commentGuestView);
				dress($prefix1 . '_input_password', 'password', $commentGuestView);
				dress($prefix1 . '_input_homepage', 'homepage', $commentGuestView);
				if (!empty($_POST["name_{$entry['id']}"]))
					$guestName = htmlspecialchars($_POST["name_{$entry['id']}"]);
				else if (!empty($_SESSION['openid']['nickname']))
					$guestName = htmlspecialchars($_SESSION['openid']['nickname']);
				else if (!empty($_COOKIE[$context->getProperty('service.cookie_prefix').'guestName']))
					$guestName = htmlspecialchars($_COOKIE[$context->getProperty('service.cookie_prefix').'guestName']);
				else
					$guestName = '';
				dress('guest_name', $guestName, $commentGuestView);
				if (!empty($_POST["homepage_{$entry['id']}"]) && $_POST["homepage_{$entry['id']}"] != 'http://') {
					if (strpos($_POST["homepage_{$entry['id']}"], 'http://') === 0)
						$guestHomepage = htmlspecialchars($_POST["homepage_{$entry['id']}"]);
					else
						$guestHomepage = 'http://' . htmlspecialchars($_POST["homepage_{$entry['id']}"]);
				} else if (!empty($_SESSION['openid']['homepage'])) {
					$guestHomepage = htmlspecialchars($_SESSION['openid']['homepage']);
				} else if (!empty($_COOKIE[$context->getProperty('service.cookie_prefix').'guestHomepage'])) {
					$guestHomepage = htmlspecialchars($_COOKIE[$context->getProperty('service.cookie_prefix').'guestHomepage']);
				}
				else
					$guestHomepage = 'http://';
				dress('guest_homepage', $guestHomepage, $commentGuestView);
				dress($prefix1 . ($isComment ? '_guest' : '_form'), $commentGuestView, $commentMemberView);
			}
			dress($prefix1 . '_input_is_secret', 'secret', $commentMemberView);
			dress($prefix1 . '_member', $commentMemberView, $commentView);

			dress($prefix1 . '_input_comment', 'comment', $commentView);
			dress($prefix1 . '_onclick_submit', "addComment(this, {$entry['id']}); return false;", $commentView);
			dress($prefix1 . '_textarea_body', 'comment', $commentView);
			dress($prefix1 . '_textarea_body_value', '', $commentView);
			dress('article_rep_id', $entry['id'], $commentView);
		} else if ($useForm == true) {
			$commentView = '';
		}

		if ($useForm == true) {
			dress($prefix1 . '_input_form', "<form id=\"entry".$entry['id']."WriteComment\" method=\"post\" action=\"".$context->getProperty('uri.blog')."/comment/add/{$entry['id']}\" onsubmit=\"return false\" style=\"margin: 0\">" . $commentView . '</form>', $commentRrevView);
			$commentView = $commentRrevView;
		} else {
			$commentView = "<form id=\"entry".$entry['id']."WriteComment\" method=\"post\" action=\"".$context->getProperty('uri.blog')."/comment/add/{$entry['id']}\" onsubmit=\"return false\" style=\"margin: 0\">" . $commentView . '</form>';
		}
	} else {
		dress($prefix1 . '_input_form', "", $commentView);
	}
	/// Adding feed links.
	dress('article_rep_rp_atomurl', $context->getProperty('uri.default').'/atom/comment/'.$entry['id'], $commentView);
	dress('article_rep_rp_rssurl', $context->getProperty('uri.default').'/rss/comment/'.$entry['id'], $commentView);
	return $commentView;
}

function getCategoriesView($totalPosts, $categories, $selected, $xhtml = false) {
	global $blogURL, $service, $blog;
	requireModel('blog.category');
	requireLibrary('blog.skin');
	$blogid = getBlogId();
	$categoryCount = 0;
	$categoryCountAll = 0;
	$parentCategoryCount = 0;
	$tree = array('id' => 0, 'label' => getCategoryNameById($blogid, 0), 'value' => $totalPosts, 'link' => "$blogURL/category", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		if(doesHaveOwnership() || getCategoryVisibility($blogid, $category1['id']) > 1) {
			foreach ($category1['children'] as $category2) {
				if( doesHaveOwnership() || getCategoryVisibility($blogid, $category2['id']) > 1) {
					array_push($children,
						array('id' => $category2['id'],
							'label' => $category2['name'],
							'value' => (doesHaveOwnership() ? $category2['entriesinlogin'] : $category2['entries']),
							'link' => "$blogURL/category/" . ($blog['useSloganOnCategory'] ? URL::encode($category2['label'],$service['useEncodedURL']) : $category2['id']),
							'rsslink' => "$blogURL/rss/category/" . ($blog['useSloganOnCategory'] ? URL::encode($category2['label'],$service['useEncodedURL']) : $category2['id']),
							'atomlink' => "$blogURL/atom/category/" . ($blog['useSloganOnCategory'] ? URL::encode($category2['label'],$service['useEncodedURL']) : $category2['id']),
							'children' => array()
						)
					);
					$categoryCount = $categoryCount + (doesHaveOwnership() ? $category2['entriesinlogin'] : $category2['entries']);
				}
				$categoryCountAll = $categoryCountAll + (doesHaveOwnership() ? $category2['entriesinlogin'] : $category2['entries']);
			}
			$parentCategoryCount = (doesHaveOwnership() ? $category1['entriesinlogin'] - $categoryCountAll : $category1['entries'] - $categoryCountAll);
			if($category1['id'] != 0) {
				array_push($tree['children'],
					array('id' => $category1['id'],
						'label' => $category1['name'],
						'value' => $categoryCount + $parentCategoryCount,
						'link' => "$blogURL/category/" . ($blog['useSloganOnCategory'] ? URL::encode($category1['label'],$service['useEncodedURL']) : $category1['id']),
						'rsslink' => "$blogURL/rss/category/" . ($blog['useSloganOnCategory'] ? URL::encode($category1['label'],$service['useEncodedURL']) : $category1['id']),
						'atomlink' => "$blogURL/atom/category/" . ($blog['useSloganOnCategory'] ? URL::encode($category1['label'],$service['useEncodedURL']) : $category1['id']),
						'children' => $children)
				);
			}
			$categoryCount = 0;
			$categoryCountAll = 0;
			$parentCategoryCount = 0;
		}
	}
	ob_start();
	printTreeView($tree, $selected, false, $xhtml);
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getCategoriesViewInOwner($totalPosts, $categories, $selected) {
	global $blogURL;
	$blogid = getBlogId();
	requireModel('blog.category');
	requireLibrary('blog.skin');
	// Initialize root category.
	$tree = array('id' => 0, 'label' => getCategoryNameById(getBlogId(), 0), 'value' => $totalPosts, 'link' => "$blogURL/owner/entry/category", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		foreach ($category1['children'] as $category2) {
			if(getCategoryVisibility($blogid, $category1['id']) == 2) {
				array_push($children, array('id' => $category2['id'], 'label' => (getCategoryVisibility($blogid, $category2['id'])==2 ? $category2['name'] : _t('(비공개)').' '.$category2['name']), 'value' =>  $category2['entriesinlogin'], 'link' => "$blogURL/owner/entry/category/?id={$category2['id']}&entries={$category2['entries']}&priority={$category1['priority']}&name1=" . rawurlencode($category2['name']) . "&name2=" . rawurlencode($category2['name']), 'children' => array()));
			} else {
				array_push($children, array('id' => $category2['id'], 'label' => '[!] '.(getCategoryVisibility($blogid, $category2['id'])==2 ? $category2['name'] : _t('(비공개)').' '.$category2['name']), 'value' =>  $category2['entriesinlogin'], 'link' => "$blogURL/owner/entry/category/?id={$category2['id']}&entries={$category2['entries']}&priority={$category1['priority']}&name1=" . rawurlencode($category2['name']) . "&name2=" . rawurlencode($category2['name']), 'children' => array()));
			}
		}
		if($category1['id'] != 0) {
			array_push($tree['children'], array('id' => $category1['id'], 'label' => (getCategoryVisibility($blogid, $category1['id'])==2 ? $category1['name'] : _t('(비공개)').' '.$category1['name']), 'value' => $category1['entriesinlogin'], 'link' => "$blogURL/owner/entry/category/?&id={$category1['id']}&entries={$category1['entries']}&priority={$category1['priority']}&name1=" . rawurlencode($category1['name']), 'children' => $children));
		}
	}
	ob_start();
	printTreeView($tree, $selected);
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getCategoriesViewInSkinSetting($totalPosts, $categories, $selected) {
	requireModel('blog.category');
	requireLibrary('blog.skin');

	$tree = array('id' => 0, 'label' => getCategoryNameById(getBlogId(), 0), 'value' => $totalPosts, 'link' => "", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		foreach ($category1['children'] as $category2) {
			array_push($children, array('id' => $category2['id'], 'label' => $category2['name'], 'value' => (doesHaveOwnership() ? $category2['entriesinlogin'] : $category2['entries']), 'link' => "", 'children' => array()));
		}
		if($category1['id'] != 0) {
			array_push($tree['children'], array('id' => $category1['id'], 'label' => $category1['name'], 'value' => (doesHaveOwnership() ? $category1['entriesinlogin'] : $category1['entries']), 'link' => "", 'children' => $children));
		}
	}
	ob_start();
	printTreeView($tree, $selected, true);
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function printTreeView($tree, $selected, $embedJava = false, $xhtml=false) {
	requireLibrary('blog.skin');
	requireModel('blog.entry');
	global $skinSetting, $defaultURL, $blog;
	$skin = $skinSetting;
	if ($embedJava == false) { // not from getCategoriesViewInSkinSetting
		$skin = getCategoriesSkin();
	}
	if ($xhtml) {
		echo '<ul>'.CRLF;
		$isSelected = ($tree['id'] === $selected) ? ' class="selected"' : '';

		echo "<li$isSelected>".CRLF;
		if ($blog['useFeedViewOnCategory'])
			echo ' <a href="'.$defaultURL.'/atom" class="categoryFeed"><span class="text">ATOM</span></a>'.CRLF;
		echo "<a href=\"", htmlspecialchars($tree['link']), '" class="categoryItem">', htmlspecialchars($tree['label']);
		if ($skin['showValue'])
			echo " <span class=\"c_cnt\">{$tree['value']}</span>";
		echo "</a>".CRLF;
		if (sizeof($tree['children']) > 0) {
			echo '<ul>'.CRLF;
			foreach($tree['children'] as $child) {
				$classNames = array();
				if ($child['id'] === $selected)
					array_push($classNames, 'selected');
				if ($child == end($tree['children']))
					array_push($classNames, 'lastChild');
				$isSelected = count($classNames) > 0 ? ' class="' . implode(' ', $classNames) . '"' : '';

				echo "<li$isSelected>".CRLF;
				if ($blog['useFeedViewOnCategory'])
					echo ' <a href="'.$child['atomlink'].'" class="categoryFeed"><span class="text">ATOM</span></a>'.CRLF;
				echo "<a href=\"", htmlspecialchars($child['link']), '" class="categoryItem">', htmlspecialchars($child['label']);
				if ($skin['showValue'])
					echo " <span class=\"c_cnt\">{$child['value']}</span>";
				echo "</a>".CRLF;

				if (sizeof($child['children']) > 0) {
					echo '<ul>'.CRLF;
					foreach($child['children'] as $leaf) {
						$classNames = array();
						if ($leaf['id'] === $selected)
							array_push($classNames, 'selected');
						if ($leaf == end($child['children']))
							array_push($classNames, 'lastChild');
						$isSelected = count($classNames) > 0 ? ' class="' . implode(' ', $classNames) . '"' : '';

						echo "<li$isSelected>".CRLF;
						if ($blog['useFeedViewOnCategory'])
							echo '<a href="'.$leaf['atomlink'].'" class="categoryFeed"><span class="text">ATOM</span></a>'.CRLF;
						echo "<a href=\"", htmlspecialchars($leaf['link']), '" class="categoryItem">', htmlspecialchars($leaf['label']);
						if ($skin['showValue'])
							echo " <span class=\"c_cnt\">{$leaf['value']}</span>";
						echo "</a>".CRLF;
						echo "</li>".CRLF;
					}
					echo '</ul>'.CRLF;
				}
				echo '</li>'.CRLF;
			}
			echo "</ul>".CRLF;
		}
		echo '</li>'.CRLF.'</ul>'.CRLF;
		return;
	}
	$action = 0;
?>
<script type="text/javascript">
	//<![CDATA[
		var expanded = false;
		function expandTree() {
<?php
	foreach ($tree['children'] as $level1) {
		if (!empty($level1['children'])) {
?>
			expandFolder(<?php echo $level1['id'];?>, true);
<?php
		}
	}
?>
		}

		function expandFolder(category, expand) {
			var oLevel1 = document.getElementById("category_" + category);
			var oImg = oLevel1.getElementsByTagName("img")[0];
			switch (expand) {
				case true:
					oImg.src = "<?php echo $skin['url'];?>/tab_opened.gif";
					showLayer("category_" + category + "_children");
					return true;
				case false:
					oImg.src = "<?php echo $skin['url'];?>/tab_closed.gif";
					hideLayer("category_" + category + "_children");
					return true;
			}
			return false;
		}

		function toggleFolder(category) {
			var oLevel1 = document.getElementById("category_" + category);
			var oImg = oLevel1.getElementsByTagName("img")[0];
			switch (oImg.src.substr(oImg.src.length - 10, 6)) {
				case "isleaf":
					return true;
				case "closed":
					oImg.src = "<?php echo $skin['url'];?>/tab_opened.gif";
					showLayer("category_" + category + "_children");
					expanded = true;
					return true;
				case "opened":
					oImg.src = "<?php echo $skin['url'];?>/tab_closed.gif";
					hideLayer("category_" + category + "_children");
					expanded = false;
					return true;
			}
			return false;
		}
		var selectedNode = 0;
		function selectNode(category) {
			try {
				var root = document.getElementById('treeComponent');
				var prevSelectedNode= root.getAttribute('currentselectednode');
				var oLevel = document.getElementById("category_" + selectedNode);
				var oChild = oLevel.getElementsByTagName("table")[0];

				oChild.style.color = "#<?php echo $skin['itemColor'];?>";
<?php
	if ($skin['itemBgColor'] != '')
		echo "				oChild.style.backgroundColor = \"#{$skin['itemBgColor']}\"";
	else
		echo "				oChild.style.backgroundColor = \"\"";
?>

				root.setAttribute('currentselectednode',category);
				document.getElementById('text_'+selectedNode).style.color="#<?php echo $skin['itemColor'];?>";

				var oLevel = document.getElementById("category_" + category);
				var oChild = oLevel.getElementsByTagName("table")[0];
				oChild.style.color = "#<?php echo $skin['activeItemColor'];?>";
<?php
	if ($skin['activeItemBgColor'] != '')
		echo "				oChild.style.backgroundColor = \"#{$skin['activeItemBgColor']}\"";
	else
		echo "				oChild.style.backgroundColor = \"\"";
?>

				document.getElementById('text_'+category).style.color="#<?php echo $skin['activeItemColor'];?>";

				selectedNode = category;
			} catch(e) {
				alert(e.message);
			}

		}

		function setTreeStyle(skin) {
			try {
				treeNodes = document.getElementsByName("treeNode");
				for(var i=0; i<treeNodes.length; i++) {
					if( ('category_'+selectedNode) == (treeNodes[i].getAttribute('id').value) ) {
						var oLevel = document.getElementById('category_'+i);
						var oChild = oLevel.getElementsByTagName("table")[0];
						oChild.style.color ='#'+skin['activeItemColor'];
						if (skin['activeItemBgColor'] != '' && skin['activeItemBgColor'] != undefined) {
							oChild.style.backgroundColor ='#'+skin['activeItemBgColor'];
						} else {
							oChild.style.backgroundColor ="";
						}
						alert(oChild.style.backgroundColor);
					} else{
						var oLevel = document.getElementById("category_" + i);
						var oChild = oLevel.getElementsByTagName("table")[0];
						oChild.style.color ='#'+skin['colorOnTree'];
						oChild.style.backgroundColor ='#'+skin['bgcolorOnTree'];
						var oLevel = document.getElementById('text_'+i).style.color='#'+skin['colorOnTree'];
						alert(document.getElementById('text_'+i).style.color);
				}
				}
			} catch(e) {
				alert(e.message);
			}
		}
	//]]>
</script>
	<?php
	if ($skin['itemBgColor'] == "") {
		$itemBgColor = '';
	} else {
		$itemBgColor = 'background-color: #' . $skin['itemBgColor'] . ';';
	}
?>
	<table id="treeComponent" <?php echo ($embedJava==true) ? 'currentselectednode="' . $selected . '"' : '';?> cellpadding="0" cellspacing="0" style="width: 100%;"><tr>
	<td>
		<table id="category_0" <?php echo ($embedJava==true) ? 'name="treeNode"' : '';?> cellpadding="0" cellspacing="0"><tr>
			<td class="ib" style="font-size: 1px"><img src="<?php echo $skin['url'];?>/tab_top.gif" width="16" onclick="expandTree()" alt="" /></td>
			<td valign="top" style="font-size:9pt; padding-left:3px">
				<table onclick="<?php
	if ($action == 1) {
?> alert(3);onclick_setimp(window, this, c_ary, t_ary); <?php
	}
?>" id="imp0" cellpadding="0" cellspacing="0" style="<?php echo $itemBgColor;?>"><tr>
					<?php
	if (empty($tree['link']))
		$link = 'onclick="selectNode(0)"';
	else
		$link = 'onclick="window.location.href=\'' . escapeJSInAttribute($tree['link']) . '\'"';
?>
					<td class="branch3" <?php echo $link;?>><div id="text_0" style=" color: #<?php echo $skin['itemColor'];?>;"><?php echo htmlspecialchars($tree['label']);?> <?php
	if ($skin['showValue'])
		print "<span class=\"c_cnt\">({$tree['value']})</span>";
?></div></td>
				</tr></table>
			</td>
		</tr></table>

<?php
	$parentOfSelected = false;
	$i = count($tree['children']);

	foreach ($tree['children'] as $row) {
		$i--;
		if (empty($row['link']))
			$link = 'onclick="selectNode(' . $row['id'] . ')"';
		else
			$link = 'onclick="window.location.href=\'' . escapeJSInAttribute($row['link']) . '\'"';
?>
		<table <?php echo ($embedJava==true) ? 'name="treeNode"' : '';?>  id="category_<?php echo $row['id'];?>" cellpadding="0" cellspacing="0"><tr>
			<td class="ib" style="width:39px; font-size: 1px; background-image: url('<?php echo $skin['url'];?>/navi_back_noactive<?php echo ($i ? '' : '_end');?>.gif')"><a class="click" onclick="toggleFolder('<?php echo $row['id'];?>')"><img src="<?php echo $skin['url'];?>/tab_<?php echo (count($row['children']) ? 'closed' : 'isleaf');?>.gif" width="39" alt="" /></a></td>
			<td>
				<table cellpadding="0" cellspacing="0" style="<?php echo $itemBgColor;?>"><tr>
					<td class="branch3" <?php echo $link;?>><div id="text_<?php echo $row['id'];?>" style="color: #<?php echo $skin['itemColor'];?>;"><?php echo htmlspecialchars(UTF8::lessenAsEm($row['label'], $skin['labelLength']));?> <?php
		if ($skin['showValue'])
			print "<span class=\"c_cnt\">({$row['value']})</span>";
?></div></td>
				</tr></table>
			</td>
		</tr></table>
		<div id="category_<?php echo $row['id'];?>_children" style="display:none">
<?php
		$j = count($row['children']);
		foreach ($row['children'] as $irow) {
			if ($irow['id'] == $selected)
				$parentOfSelected = $row['id'];
			$j--;
			if (empty($irow['link']))
				$link = 'onclick="selectNode(' . $irow['id'] . ')"';
			else
				$link = 'onclick="window.location.href=\'' . escapeJSInAttribute($irow['link']) . '\'"';
			if (empty($irow['link']))
				$link = 'onclick="selectNode(' . $irow['id'] . ')"';
			else
				$link = 'onclick="window.location.href=\'' . escapeJSInAttribute($irow['link']) . '\'"';
?>
				<table id="category_<?php echo $irow['id'];?>" <?php echo ($embedJava==true) ? 'name="treeNode"' : '';?> cellpadding="0" cellspacing="0"><tr>
				<td style="width:39px; font-size: 1px"><img src="<?php echo $skin['url'];?>/navi_back_active<?php echo ($i ? '' : '_end');?>.gif" width="17" height="18" alt="" /><img src="<?php echo $skin['url'];?>/tab_treed<?php
			if (!$j)
				print "_end";
?>.gif" width="22" alt="" /></td>
				<td>
					<table <?php echo $link;?> cellpadding="0" cellspacing="0" style="<?php echo $itemBgColor;?>"><tr>
					<td class="branch3"><div id="text_<?php echo $irow['id'];?>" style="color: #<?php echo $skin['itemColor'];?>;"><?php echo htmlspecialchars(UTF8::lessenAsEm($irow['label'], $skin['labelLength']));?> <?php echo ($skin['showValue'] ? "<span class=\"c_cnt\">({$irow['value']})</span>" : '');?></div></td>
					</tr></table>
				</td>
				</tr></table>
<?php
		}
?>
		</div>
<?php
	}
?>
	</td></tr></table>
<?php
	if (is_numeric($selected)) {
?>
<script type="text/javascript">
//<![CDATA[
	function execOnLoadSelect() {
<?php
		if ($parentOfSelected) {
?>
	expandFolder(<?php echo $parentOfSelected;?>, true);
<?php
		}
?>
	selectNode(<?php echo $selected;?>);
	}
	window.addEventListener("load", execOnLoadSelect, false);
//]]>
</script>
<?php
	}
}

function getArchivesView($archives, $template) {
	global $blogURL;
	ob_start();
	foreach ($archives as $archive) {
		$view = "$template";
		dress('archive_rep_link', "$blogURL/archive/{$archive['period']}", $view);
		dress('archive_rep_date', fireEvent('ViewArchiveDate', getPeriodLabel($archive['period']), $archive['period']), $view);
		dress('archive_rep_count', $archive['count'], $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getCalendarView($calendar) {
	global $blogURL;
	$current = $calendar['year'] . $calendar['month'];
	$previous = addPeriod($current, - 1);
	$next = addPeriod($current, 1);
	$firstWeekday = date('w', mktime(0, 0, 0, $calendar['month'], 1, $calendar['year']));
	$lastDay = date('t', mktime(0, 0, 0, $calendar['month'], 1, $calendar['year']));
	$today = ($current == Timestamp::get('Ym') ? Timestamp::get('j') : null);

	$currentMonthStr = fireEvent('ViewCalendarHead', Timestamp::format('%Y/%m', getTimeFromPeriod($current)));

	ob_start();
?>
<table class="tt-calendar" cellpadding="0" cellspacing="1" style="width: 100%; table-layout: fixed">
	<caption class="cal_month">
		<a href="<?php echo $blogURL;?>/archive/<?php echo $previous;?>" title="<?php echo _text('1개월 앞의 달력을 보여줍니다.');?>">&laquo;</a>
		&nbsp;
		<a href="<?php echo $blogURL;?>/archive/<?php echo $current;?>" title="<?php echo _text('현재 달의 달력을 보여줍니다.');?>"><?php echo $currentMonthStr;?></a>
		&nbsp;
		<a href="<?php echo $blogURL;?>/archive/<?php echo $next;?>" title="<?php echo _text('1개월 뒤의 달력을 보여줍니다.');?>">&raquo;</a>
	</caption>
	<thead>
		<tr>
			<th class="cal_week2"><?php echo fireEvent('ViewCalendarHeadWeekday', _text('일요일'));?></th>
			<th class="cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('월요일'));?></th>
			<th class="cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('화요일'));?></th>
			<th class="cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('수요일'));?></th>
			<th class="cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('목요일'));?></th>
			<th class="cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('금요일'));?></th>
			<th class="cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('토요일'));?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$day = 0;
	$totalDays = $firstWeekday + $lastDay;
	$lastWeek = ceil($totalDays / 7);

	for ($week=0; $week<$lastWeek; $week++) {
		// 주중에 현재 날짜가 포함되어 있으면 주를 현재 주 class(tt-current-week)를 부여한다.
		if (($today + $firstWeekday) > $week * 7 && ($today + $firstWeekday) <= ($week + 1) * 7) {
			echo '		<tr class="cal_week cal_current_week">'.CRLF;
		} else {
			echo '		<tr class="cal_week">'.CRLF;
		}

		for($weekday=0; $weekday<7; $weekday++) {
			$day++;
			$dayString = isset($calendar['days'][$day]) ? '<a class="cal_click" href="'.$blogURL.'/archive/'.$current.($day > 9 ? $day : "0$day").'">'.$day.'</a>' : $day;

			// 일요일, 평일, 토요일별로 class를 부여한다.
			switch ($weekday) {
				case 0:
					$className = " cal_day cal_day_sunday";
					break;
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
					$className = " cal_day";
					break;
			}

			// 오늘에 현재 class(tt-current-day)를 부여한다.
			$className .= $day == $today ? " cal_day4" : " cal_day3";

			if ($week == 0) {
				if ($weekday < $firstWeekday) {
					$day--;
					// 달의 첫째날이 되기 전의 빈 칸.
					echo '			<td class="cal_day1">&nbsp;</td>'.CRLF;
				} else {
					echo '			<td class="'.$className.'">'.$dayString.'</td>'.CRLF;
				}
			} else if ($week == ($lastWeek - 1)) {
				if ($day <= $lastDay) {
					echo '			<td class="'.$className.'">'.$dayString.'</td>'.CRLF;
				} else {
					// 달의 마지막날을 넘어간 날짜 빈 칸.
					echo '			<td class="cal_day2">&nbsp;</td>'.CRLF;
				}
			} else {
				echo '			<td class="'.$className.'">'.$dayString.'</td>'.CRLF;
			}
		}
		echo '		</tr>'.CRLF;

		if ($day >= $lastDay) {
			break;
		}
	}
?>
	</tbody>
</table>
<?php
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getAuthorListView($authorInfo, $template) {
	global $blog, $service, $blogURL, $skinSetting, $contentContainer;
	ob_start();
	foreach ($authorInfo as $user) {
		$view = "$template";
		$permalink = "$blogURL/author/" . rawurlencode($user['name']);
		dress('author_rep_link', $permalink, $view);
		dress('author_rep_name', $user['name'], $view);
//		dress('author_rep_post_count', $user['postcount'], $view);

		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();

	return $view;
}

function getRecentNoticesView($notices, $noticeView, $noticeItemView, $isPage = false) {
	global $blog, $service, $blogURL, $skinSetting, $contentContainer;
	if($isPage) $prefix = 'page'; else $prefix = 'notice';
	if (sizeof($notices) > 0) {
		$itemsView = '';
		foreach ($notices as $notice) {
			$itemView = $noticeItemView;
			dress($prefix.'_rep_title', htmlspecialchars(fireEvent('View'.$prefix.'Title', UTF8::lessenAsEm($notice['title'], $skinSetting['recentNoticeLength']), $notice['id'])), $itemView);
			if($blog['useSloganOnPost']) {
				if(isset($notice['slogan'])&& !empty($notice['slogan'])) {
					$noticeURL = URL::encode($notice['slogan']);
				} else {
					$noticeURL = URL::encode($notice['title']);
				}
			} else {
				$noticeURL = $notice['id'];
			}
			$name = User::getName($notice['userid']);
			dress($prefix.'_rep_link', "$blogURL/".$prefix."/$noticeURL", $itemView);
			dress($prefix.'_rep_author', $name, $itemView);
			dress($prefix.'_rep_author', $blogURL."/author/".rawurlencode($name), $itemView);
			$itemsView .= $itemView;
		}
		dress('rct_'.$prefix.'_rep', $itemsView, $noticeView);
		// IE webslice support
		if(Setting::getBlogSettingGlobal('useMicroformat',3) == 3) {
			$noticeView = addWebSlice($noticeView, 'recentNoticeWebslice', htmlspecialchars($blog['title'].' - '._t('최근 공지')));
		}
	}
	return $noticeView;
}

function getRecentEntriesView($entries, $entriesView = null, $template) {
	global $blog, $service, $blogURL, $skinSetting, $contentContainer;
	$recentEntriesView = '';
	foreach ($entries as $entry) {
		$view = "$template";
		$permalink = "$blogURL/" . ($blog['useSloganOnPost'] ? "entry/" . URL::encode($entry['slogan'],$service['useEncodedURL']) : $entry['id']);
		dress('rctps_rep_link', $permalink, $view);
		$contentContainer["recent_entry_{$entry['id']}"] = htmlspecialchars(UTF8::lessenAsEm($entry['title'], $skinSetting['recentEntryLength']));
		dress('rctps_rep_title', setTempTag("recent_entry_{$entry['id']}"), $view);
		$name = User::getName($entry['userid']);
		dress('rctps_rep_author',  $name, $view);
		dress('rctps_rep_author_link', $blogURL."/author/" . rawurlencode($name), $view);
		dress('rctps_rep_time', fireEvent('ViewRecentPostDate', Timestamp::format2($entry['published']), $entry['published']), $view);
		dress('rctps_rep_rp_cnt', "<span id=\"commentCountOnRecentEntries{$entry['id']}\">".($entry['comments'] > 0 ? "{$entry['comments']}" : '').'</span>', $view);
		$recentEntriesView .= $view;
	}
	if(!is_null($entriesView)) {
		dress('rctps_rep',$recentEntriesView, $entriesView);
		// IE webslice support
		if(Setting::getBlogSettingGlobal('useMicroformat',3) == 3) {
			$recentEntriesView = addWebSlice($entriesView, 'recentEntriesWebslice', htmlspecialchars($blog['title'].' - '._t('최근 글')));
		} else return $entriesView;
	}
	return $recentEntriesView;
}

function getRecentCommentsView($comments, $commentView = null, $template) {
	global $blog, $service, $blogURL, $skinSetting, $contentContainer;
	$recentCommentView = '';
	foreach ($comments as $comment) {
		$view = "$template";
		dress('rctrp_rep_link', "$blogURL/".($blog['useSloganOnPost'] ? "entry/".URL::encode($comment['slogan'],$service['useEncodedURL']) : $comment['entry'])."?commentId=".$comment['id']."#comment{$comment['id']}", $view);
		$contentContainer["recent_comment_{$comment['id']}"] = htmlspecialchars(UTF8::lessenAsEm(strip_tags($comment['comment']), $skinSetting['recentCommentLength']));
		dress('rctrp_rep_desc', setTempTag("recent_comment_{$comment['id']}"), $view);
		dress('rctrp_rep_time', fireEvent('ViewRecentCommentDate', Timestamp::format2($comment['written']), $comment['written']), $view);
		dress('rctrp_rep_name', htmlspecialchars(UTF8::lessenAsEm($comment['name'], $skinSetting['recentCommentLength'])), $view);
		$recentCommentView .= $view;
	}
	if(!is_null($commentView)) {
		dress('rctrp_rep',$recentCommentView, $commentView);
		// IE webslice support
		if(Setting::getBlogSettingGlobal('useMicroformat',3) == 3) {
			$recentCommentView = addWebSlice($commentView, 'recentCommentWebslice', htmlspecialchars($blog['title'].' - '._t('최근 댓글')));
		} else return $commentView;
	}
	return $recentCommentView;
}

function getRecentTrackbacksView($trackbacks, $trackbackView = null, $template) {
	global $blogURL, $blog, $skinSetting, $service;
	$recentTrackbackView = '';
	foreach ($trackbacks as $trackback) {
		$view = "$template";
		dress('rcttb_rep_link', "$blogURL/".($blog['useSloganOnPost'] ? "entry/".URL::encode($trackback['slogan'],$service['useEncodedURL']) : $trackback['entry'])."#trackback{$trackback['id']}", $view);

		dress('rcttb_rep_desc', htmlspecialchars(UTF8::lessenAsEm($trackback['subject'], $skinSetting['recentTrackbackLength'])), $view);
		dress('rcttb_rep_time', fireEvent('ViewRecentTrackbackDate', Timestamp::format2($trackback['written']), $trackback['written']), $view);
		dress('rcttb_rep_name', htmlspecialchars(UTF8::lessenAsEm($trackback['site'], $skinSetting['recentTrackbackLength'])), $view);
		$recentTrackbackView .= $view;
	}
	if(!is_null($trackbackView)) {
		dress('rcttb_rep',$recentTrackbackView, $trackbackView);
		// IE webslice support
		if(Setting::getBlogSettingGlobal('useMicroformat',3) == 3) {
			$recentTrackbackView = addWebSlice($trackbackView, 'recentCommentWebslice', htmlspecialchars($blog['title'].' - '._t('최근 트랙백')));
		} else return $trackbackView;
	}
	return $recentTrackbackView;
}

function addWebSlice($content, $id, $title) {
	return '<div class="hslice" id="'.$id.'" style="margin:0;padding:0;">'.CRLF.
		'<h4 class="entry-title" style="visibility:hidden;height:0;margin:0;padding:0;">'.$title.'</h4>'.CRLF.
		'<div class="entry-content" style="margin:0;padding:0;">'.CRLF.$content.CRLF.'</div>'.CRLF.
		'</div>'.CRLF;
}

function addXfnAttrs( $url, $xfn, & $view ) {
	$view = str_replace( "href=\"$url\"", "href=\"$url\" rel=\"$xfn\"", $view);
	$view = str_replace( "href='$url'", "href='$url' rel=\"$xfn\"", $view);
}

function getLinksView($links, $template) {
	global $blogURL, $skinSetting, $suri, $pathURL;
	if( rtrim( $suri['url'], '/' ) == $pathURL ) {
		$home = true;
	} else {
		$home = false;
	}
	ob_start();
	$showXfn = (Setting::getBlogSettingGlobal('useMicroformat',3) > 1);
	foreach ($links as $link) {
		if((!doesHaveOwnership() && $link['visibility'] == 0) ||
			(!doesHaveMembership() && $link['visibility'] < 2)) {
			continue;
		}
		$view = "$template";
		dress('link_url', htmlspecialchars($link['url']), $view);
		dress('link_category', htmlspecialchars($link['categoryName']), $view);
		if( $showXfn && $home && $link['xfn'] ) {
			addXfnAttrs( htmlspecialchars($link['url']), htmlspecialchars($link['xfn']), $view );
		}
		dress('link_site', fireEvent('ViewLink', htmlspecialchars(UTF8::lessenAsEm($link['name'], $skinSetting['linkLength']))), $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getLinkListView($links) {
	global $blogURL, $skinSetting, $suri, $pathURL;
	if( rtrim( $suri['url'], '/' ) == $pathURL ) {
		$home = true;
	} else {
		$home = false;
	}
	$categoryName = null;
	$buffer = '<ul>'.CRLF;
	$showXfn = (Setting::getBlogSettingGlobal('useMicroformat',3) > 1);
	foreach ($links as $link) {
		if((!doesHaveOwnership() && $link['visibility'] == 0) ||
			(!doesHaveMembership() && $link['visibility'] < 2)) {
			continue;
		}
		if($categoryName != $link['categoryName']) {
			if(!empty($categoryName)) $buffer .= '</ul>'.CRLF.'</li>'.CRLF;
			$categoryName = $link['categoryName'];
			$buffer .= '<li><span class="link_ct">'.htmlspecialchars($link['categoryName']).'</span>'.CRLF
				.'<ul>'.CRLF;
		}
		if( $showXfn && $home && $link['xfn'] ) {
			addXfnAttrs( htmlspecialchars($link['url']), htmlspecialchars($link['xfn']), $link['url']);
		}
		$buffer .= '<li><a href="'.htmlspecialchars($link['url']).'">'.fireEvent('ViewLink', htmlspecialchars(UTF8::lessenAsEm($link['name'], $skinSetting['linkLength']))).'</a></li>'.CRLF;
	}
	if(!empty($categoryName)) $buffer .= '</ul>'.CRLF.'</li>'.CRLF;
	$buffer .='</ul>'.CRLF;
	return $buffer;
}

function getRandomTagsView($tags, $template) {
	global $blogURL, $service;
	ob_start();
	list($maxTagFreq, $minTagFreq) = getTagFrequencyRange();
	foreach ($tags as $tag) {
		$view = $template;
		dress('tag_link', "$blogURL/tag/" . (Setting::getBlogSettingGlobal('useSloganOnTag',true) ? URL::encode($tag['name'],$service['useEncodedURL']) : $tag['id']), $view);
		dress('tag_name', htmlspecialchars($tag['name']), $view);
		dress('tag_class', "cloud" . getTagFrequency($tag, $maxTagFreq, $minTagFreq), $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getEntryContentView($blogid, $id, $content, $formatter, $keywords = array(), $type = 'Post', $useAbsolutePath = true, $bRssMode = false) {
	global $hostURL, $service;
	requireModel('blog.attachment');
	requireModel('blog.keyword');
	requireLibrary('blog.skin');
	$context  = Model_Context::getInstance();
	$cacheKey = 'entry-'.$id.'-'.$type.($bRssMode ? 'format' : 'summarize').($useAbsolutePath ? 'absoultePath' : 'relativePath').($context->getProperty('blog.displaymode','desktop'));
	$cache = pageCache::getInstance();
	$cache->reset($cacheKey);
	if(!defined('__TEXTCUBE_NO_ENTRY_CACHE__') && $cache->load()) {	// If cached content exists.
		$view = $cache->contents;
	} else {	// No cache is found.
		$content = fireEvent('Format' . $type . 'Content', $content, $id);
		$func = ($bRssMode ? 'summarizeContent' : 'formatContent');
		$view = $func($blogid, $id, $content, $formatter, $keywords, $useAbsolutePath);
		
		if(!$useAbsolutePath)
			$view = avoidFlashBorder($view);

		if (!empty($keywords) && is_array($keywords)) $view = bindKeywords($keywords, $view);


		// image resampling
		if (Setting::getBlogSettingGlobal('resamplingDefault') == true) {
			preg_match_all("@<img.+src=['\"](.+)['\"](.*)/?>@Usi", $view, $images, PREG_SET_ORDER);
			$view = preg_replace("@<img.+src=['\"].+['\"].*/?>@Usi", '[#####_#####_#####_image_#####_#####_#####]', $view);
			$contentWidth = Misc::getContentWidth();

			if (count($images) > 0) {
				for ($i=0; $i<count($images); $i++) {
					if (strtolower(Misc::getFileExtension($images[$i][1])) == 'gif') {
						$view = preg_replace('@\[#####_#####_#####_image_#####_#####_#####\]@', $images[$i][0], $view, 1);
						continue;
					}

					$attributes = preg_match('/(style="cursor: pointer;" onclick="open_img\((.[^"]+)\); return false;")/si', $images[$i][2], $matches) ? ' '.$matches[1] : '';
					$attributes .= preg_match('/(alt="([^"]*)")/si', $images[$i][2], $matches) ? ' '.$matches[1] : ' alt="resize"';
					$attributes .= preg_match('/(title="([^"]*)")/si', $images[$i][2], $matches) ? $title = ' '.$matches[1] : '';

					$tempFileName = array_pop(explode('/', $images[$i][1]));
					if (preg_match('/(.+)\.w(\d{1,})\-h(\d{1,})\.(.+)/', $tempFileName, $matches))
						$tempFileName = $matches[1].'.'.$matches[4];

					$newImage = $images[$i][0];
					if (file_exists(__TEXTCUBE_ATTACH_DIR__."/{$blogid}/{$tempFileName}")) {
						$tempAttributes = Misc::getAttributesFromString($images[$i][2]);
						$tempOriginInfo = getimagesize(__TEXTCUBE_ATTACH_DIR__."/{$blogid}/{$tempFileName}");

						// original image
						$absolute = isset($options['absolute']) ? $options['absolute'] : true;
						$origImageSrc = __TEXTCUBE_ATTACH_DIR__."/{$blogid}/{$tempFileName}";
						$origImageURL = ($absolute ? $context->getProperty('uri.service'):$context->getProperty('uri.path'))."/attach/{$blogid}/{$tempFileName}";
						
						// Detect orientation
						$imageOrientation = ($tempOriginInfo[0]>=$tempOriginInfo[1] ? "landscape" : "portrait");

						// Check whether original image width is larger than resized image (blog content width)
						$imageZoomable = ($tempOriginInfo[0]>$tempAttributes['width'] ? "true" : "false"); 

						if (isset($tempAttributes['width'])) {
							$image = Utils_Image::getInstance();

							// if responsive resampling option is active & larger than 360px
							if ((Setting::getBlogSettingGlobal('resamplingResponsive') == true) && ($tempOriginInfo[0] > 360)) {
								$image = Utils_Image::getInstance();
								list($smallImageURL, $smallImageWidth, $smallImageHeight, $smallImageSrc) = $image->getImageResizer($tempFileName, array('width' => 360));
								
								if ($tempOriginInfo[0] > 800) { // if larger than 800px, generate additional size
									list($mediumImageURL, $mediumImageWidth, $mediumImageHeight, $mediumImageSrc) = $image->getImageResizer($tempFileName, array('width' => 800));

									$srcset = "{$smallImageURL} 360w, {$mediumImageURL} 800w, {$origImageURL} {$tempOriginInfo[0]}w";			
								} else {
									$srcset = "{$smallImageURL} 360w, {$origImageURL} {$tempOriginInfo[0]}w";
								}

								list($resizedImageURL, $resizedImageWidth, $resizedImageHeight, $resizedImageSrc) = $image->getImageResizer($tempFileName, array('width' => $tempAttributes['width']));

								// use default resized image for src for fallback
								$newImage = "<img src=\"{$resizedImageURL}\" srcset=\"{$srcset}\" width=\"{$tempOriginInfo[0]}\" height=\"{$tempOriginInfo[1]}\" {$attributes} data-orientation=\"{$imageOrientation}\" data-zoomable=\"{$imageZoomable}\" />";
							} 

							// if using default option & image is larger than content
							else if ($tempOriginInfo[0] > $tempAttributes['width']) {
								list($resizedImageURL, $resizedImageWidth, $resizedImageHeight, $resizedImageSrc) = $image->getImageResizer($tempFileName, array('width' => $tempAttributes['width']));
								$newImage = "<img src=\"{$resizedImageURL}\" width=\"{$resizedImageWidth}\" height=\"{$resizedImageHeight}\" {$attributes} data-orientation=\"{$imageOrientation}\" data-zoomable=\"{$imageZoomable}\"/>";
							} 
							// if image is smaller than content
							else {
								$newImage = "<img src=\"{$origImageURL}\" width=\"{$tempOriginInfo[0]}\" height=\"{$tempOriginInfo[1]}\" {$attributes} data-orientation=\"{$imageOrientation}\" data-zoomable=\"{$imageZoomable}\"/>";
							}
						
						}

						
						
					
					}
					$view = preg_replace('@\[#####_#####_#####_image_#####_#####_#####\]@', $newImage, $view, 1);
				}
			}
		}
		$cache->contents = $view;
		$cache->update();
	}
	$cache->reset();
	$view = fireEvent('View' . $type . 'Content', $view, $id);
	return $view;
}

function printEntryContentView($blogid, $id, $content, $formatter, $keywords = array()) {
	print (getEntryContentView($blogid, $id, $content, $formatter, $keywords));
}

function printFeedGroups($blogid, $selectedGroup = 0, $starredOnly = false, $searchKeyword = null) {
	global $service;
?>
													<div id="groupAdder">
														<div class="title"><span class="text"><?php echo _t('그룹 등록하기');?></span></div>
														<div class="button-box">
															<input type="text" id="newGroupTitle" class="input-text" value="<?php echo _t('그룹을 추가하세요.');?>" onfocus="if(this.value == '<?php echo _t('그룹을 추가하세요.');?>') this.value = ''" onblur="if(this.value == '') this.value = '<?php echo _t('그룹을 추가하세요.');?>'" onkeydown="if(event.keyCode==13) Reader.addGroup(this.value)" />
															<a class="add-button button" href="#void" onclick="Reader.addGroup(document.getElementById('newGroupTitle').value)"><span class="text"><?php echo _t('추가');?></span></a>
														</div>
													</div>

													<ul id="groupList">
<?php
	$count = 0;
	foreach (getFeedGroups($blogid, $starredOnly, $searchKeyword) as $group) {
		if ($group['id'] == 0)
			$group['title'] = _t('전체보기');
		$className = ($count % 2) == 1 ? 'even-line' : 'odd-line';
		$className .= ($selectedGroup == $group['id']) ? ' active-class' : ' inactive-class';
?>
														<li id="groupList<?php echo $group['id'];?>" class="<?php echo $className;?>" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
															<div class="title">
																<a href="#void" onclick="Reader.selectGroup(this, <?php echo $group['id'];?>)"><?php echo htmlspecialchars($group['title']);?></a>
															</div>
<?php
		if ($group['id']) {
?>

															<div class="button-box">
																<a class="edit-button button" href="#void" onclick="Reader.editGroup(<?php echo $group['id'];?>, '<?php echo escapeJSInAttribute($group['title']);?>'); return false;" title="<?php echo _t('이 그룹 정보를 수정합니다.');?>"><span class="text"><?php echo _t('수정');?></span></a>
															</div>
<?php
		}
?>
														</li>
<?php
		$count++;
	}
?>
													</ul>

													<div id="groupEditor" style="display: none;">
														<div class="title"><span><?php echo _t('그룹 수정하기');?></span></div>
														<div class="input-box">
															<div class="input-field">
																<input type="text" id="changeGroupTitle" class="input-text" name="changeGroupTitle" />
															</div>
															<div class="button-box">
																<input type="button" class="delete-button input-button" value="<?php echo _t('삭제');?>" onclick="Reader.deleteGroup(); return false;" />
																<span class="divider">|</span>
																<input type="submit" class="edit-button input-button" value="<?php echo _t('저장');?>" onclick="Reader.editGroupExecute(); return false;" />
																<span class="divider">|</span>
																<input type="button" class="cancel-button input-button" value="<?php echo _t('취소');?>" onclick="Reader.cancelEditGroup(); return false;" />
															</div>
														</div>
													</div>
<?php
}

function printFeeds($blogid, $group = 0, $starredOnly = false, $searchKeyword = null) {
	global $service;
?>
													<div id="feedAdder">
														<div class="title"><span><?php echo _t('피드 등록하기');?></span></div>
														<div class="button-box">
															<input type="text" id="newFeedURL" class="input-text" name="newFeedURL" value="<?php echo _t('피드 주소를 입력하세요.');?>" onfocus="if(this.value == '<?php echo _t('피드 주소를 입력하세요.');?>') this.value = ''" onblur="if(this.value == '') this.value = '<?php echo _t('피드 주소를 입력하세요.');?>'" onkeydown="if(event.keyCode==13) Reader.addFeed(this.value)" />
															<a class="add-button button" href="#void" onclick="Reader.addFeed(document.getElementById('newFeedURL').value)"><span class="text"><?php echo _t('추가');?></span></a>
															<?php echo fireEvent('AddFeedURLToolbox', '');?>
														</div>
													</div>

													<ul id="feedList">
<?php
	$count = 0;
	foreach (getFeeds($blogid, $group, $starredOnly, $searchKeyword) as $feed) {
		if ($feed['modified'] > time() - 86400)
			$status = 'Update';
		else if ($feed['modified'] == 0)
			$status = 'Failure';
		else
			$status = 'UpdateNo';
		$className = ($count % 2) == 1 ? 'even-line' : 'odd-line';
?>
														<li class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="Reader.selectFeed(this, <?php echo $feed['id'];?>)">
															<div class="title"><?php echo $feed['blogURL'] ? '<a href="' . htmlspecialchars($feed['blogURL']) . '" onclick="window.open(this.href); event.cancelBubble=true; return false;" title="'._t('이 피드의 원본 사이트를 새 창으로 엽니다.').'">' : '';?><?php echo htmlspecialchars($feed['title']);?><?php echo $feed['blogURL'] ? "</a>\n" : '';?></div>
															<div class="description"><?php echo $feed['description']?'<span class="divider"> | </span>':'&nbsp;';?><?php echo htmlspecialchars($feed['description']);?></div>
															<div class="button-box">
																<a id="iconFeedStatus<?php echo $feed['id'];?>" class="update-button button" onclick="Reader.updateFeed(<?php echo $feed['id'];?>, '<?php echo _t('피드를 갱신 했습니다.');?>'); event.cancelBubble=true; return false;" title="<?php echo _t('이 피드를 갱신 합니다.');?>"><span class="text"><?php echo _t('피드 갱신');?></span></a>
																<span class="divider">|</span>
																<a class="edit-button button" href="#void" onclick="Reader.editFeed(<?php echo $feed['id'];?>, '<?php echo escapeJSInAttribute($feed['xmlurl']);?>')" title="<?php echo _t('이 피드 정보를 수정합니다.');?>"><span class="text"><?php echo _t('수정');?></span></a>
															</div>
														</li>
<?php
		$count++;
	}
?>
													</ul>

													<div id="feedEditor" style="display: none;">
														<div class="title"><span class="text"><?php echo _t('피드 수정하기');?></span></div>
														<div class="input-box">
															<div class="input-field">
																<select id="changeFeedGroup">
<?php
	foreach (getFeedGroups($blogid) as $group) {
		if ($group['id'] == 0)
			$group['title'] = _t('그룹 없음');
?>
																	<option value="<?php echo $group['id'];?>"><?php echo htmlspecialchars($group['title']);?></option>
<?php
	}
?>
																</select>
																<input type="text" id="changeFeedURL" class="text-readonly-input" readonly="readonly" />
															</div>
															<div class="button-box">
																<input type="button" class="delete-button input-button" value="<?php echo _t('삭제');?>" onclick="Reader.deleteFeed(); return false;" />
																<span class="divider">|</span>
																<input type="submit" class="edit-button input-button" value="<?php echo _t('저장');?>" onclick="Reader.editFeedExecute(); return false;" />
																<span class="divider">|</span>
																<input type="button" class="cancel-button input-button" value="<?php echo _t('취소');?>" onclick="Reader.cancelEditFeed(); return false;" />
															</div>
														</div>
													</div>
<?php
}



function printFeedEntries($blogid, $group = 0, $feed = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null) {
	global $service;
?>
												<script type="text/javascript">
													//<![CDATA[
														var scrapedPostText = "<?php echo _t('스크랩 포스트');?>";
														var disscrapedPostText = "<?php echo _t('미스크랩 포스트');?>";
													//]]>
												</script>

												<table cellpadding="0" cellspacing="0">
													<tbody>
<?php
	$count = 0;
	foreach (getFeedEntries($blogid, $group, $feed, $unreadOnly, $starredOnly, $searchKeyword) as $entry) {
		if ($count == 0)
			$firstEntryId = $entry['id'];
		$className = $entry['wasread'] ? 'read' : 'unread';
		$className .= ($count % 2) == 1 ? ' even-line' : ' odd-line';
		$className .= ($count == 0) ? ' active-class' : ' inactive-class';
		$podcast = $entry['enclosure'] ? '<span class="podcast-icon bullet" title="'._t('팟캐스트 포스트입니다.').'"><span class="text">' . _t('팟캐스트') . '</span></span>' : '';
?>
														<tr id="entrytitleList<?php echo $entry['id'];?>" class="<?php echo $className;?>" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="Reader.selectEntry(<?php echo $entry['id'];?>)">
															<td>
																<div class="icons">
<?php
			if ($entry['item']) {
?>
																	<span id="star<?php echo $entry['id'];?>" class="scrap-on-icon bullet" title="<?php echo _t('이 포스트를 스크랩 해제합니다.');?>" onclick="Reader.toggleStarred(<?php echo $entry['id'];?>)"><span class="text"><?php echo _t('스크랩 포스트');?></span></span>
<?php
			} else {
?>
																	<span id="star<?php echo $entry['id'];?>" class="scrap-off-icon bullet" title="<?php echo _t('이 포스트를 스크랩합니다.');?>" onclick="Reader.toggleStarred(<?php echo $entry['id'];?>)"><span class="text"><?php echo _t('미스크랩 포스트');?></span></span>
<?php
			}
?>
																	<?php echo $podcast;?>
																</div>
																<div class="content">
																	<div class="title"><span class="text"><?php echo htmlspecialchars($entry['entry_title']);?></span></div>
																	<div class="blog"><?php echo htmlspecialchars($entry['blog_title']);?></div>
																</div>
															</td>
														</tr>
<?php
		$count++;
	}
?>
													</tbody>
												</table>

												<div id="additionalFeedContainer"></div>
												<div id="feedLoadingIndicator" class="system-message" style="display: none;">
													<?php echo _t('피드를 읽어오고 있습니다.');?>
												</div>

												<script type="text/javascript">
													//<![CDATA[
														Reader.setShownEntries(<?php echo $count;?>);
														Reader.setTotalEntries(<?php echo getFeedEntriesTotalCount($blogid, $group, $feed, $unreadOnly, $starredOnly, $searchKeyword);?>);
<?php
	if (isset($firstEntryId)) {
?>
														Reader.selectedEntryObject = document.getElementById("entrytitleList<?php echo $firstEntryId;?>").parentNode;
<?php
	}
?>
													//]]>
												</script>
<?php
	return $count;
}

function printFeedEntriesMore($blogid, $group = 0, $feed = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null, $offset) {
	global $service;
?>
												<table cellpadding="0" cellspacing="0">
<?php
	$count = 0;
	foreach (getFeedEntries($blogid, $group, $feed, $unreadOnly, $starredOnly, $searchKeyword, $offset) as $entry) {
		$class = $entry['wasread'] ? 'read' : 'unread';
		$class .= ($count % 2) == 1 ? ' odd-line' : ' even-line';
		$class .= ' inactive-class';
		$podcast = $entry['enclosure'] ? '<span class="podcast-icon bullet" title="'._t('팟캐스트 포스트입니다.').'"><span class="text">' . _t('팟캐스트') . '</span></span>' : '';
?>
													<tr id="entrytitleList<?php echo $entry['id'];?>" class="<?php echo $class;?>" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="Reader.selectEntry(<?php echo $entry['id'];?>)">
														<td>
															<div class="icons">
<?php
		if ($entry['item']) {
?>
																<span id="star<?php echo $entry['id'];?>" class="scrap-on-icon bullet" title="<?php echo _t('이 포스트를 스크랩 해제합니다.');?>" onclick="Reader.toggleStarred(<?php echo $entry['id'];?>)"><span class="text"><?php echo _t('스크랩 포스트');?></span></span>
<?php
		} else {
?>
																<span id="star<?php echo $entry['id'];?>" class="scrap-off-icon bullet" title="<?php echo _t('이 포스트를 스크랩합니다.');?>" onclick="Reader.toggleStarred(<?php echo $entry['id'];?>)"><span class="text"><?php echo _t('미스크랩 포스트');?></span></span>
<?php
		}
?>
																<?php echo $podcast;?>
															</div>
															<div class="content">
																<div class="title"><?php echo htmlspecialchars($entry['entry_title']);?></div>
																<div class="blog"><?php echo htmlspecialchars($entry['blog_title']);?></div>
															</div>
														</td>
													</tr>
<?php
		$count++;
	}
?>
												</table>
<?php
	return $count;
}

function printFeedEntry($blogid, $group = 0, $feed = 0, $entry = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null, $position = 'current') {
	global $service;
	if (!$entry = getFeedEntry($blogid, $group, $feed, $entry, $unreadOnly, $starredOnly, $searchKeyword, $position)) {
		$entry = array('id' => 0, 'author' => 'Textcube', 'blog_title' => 'Textcube Reader', 'permalink' => '#', 'entry_title' => _t('포스트가 없습니다.'), 'language' => 'en-US', 'description' => '<div style="height: 369px"></div>', 'tags' => '', 'enclosure' => '', 'written' => time());
	}
?>
												<div id="entryHead">
													<div class="title"><a href="<?php echo htmlspecialchars($entry['permalink']);?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($entry['entry_title']);?></a></div>
													<div class="writing-info"><span class="by">by </span><span class="name"><?php echo htmlspecialchars($entry['author'] ? preg_replace('/^\((.+)\)$/', '$1', $entry['author']) : $entry['blog_title']);?></span><span class="divider"> : </span><span class="date"><?php echo date('Y-m-d H:i:s', $entry['written']);?></span></div>
													<div class="open"><a id="entryPermalink" href="<?php echo htmlspecialchars($entry['permalink']);?>" onclick="window.open(this.href); return false;" title="<?php echo _t('이 포스트를 새 창으로 엽니다.');?>"><span class="text"><?php echo _t('새 창으로');?></span></a></div>
												</div>

												<div id="entryBody" xml:lang="<?php echo htmlspecialchars($entry['language']);?>">
<?php
	if ($entry['enclosure']) {
		if (preg_match('/\.mp3$/i', $entry['enclosure'])) {
?>
													<p><span class="podcast-icon bullet"><span class="text"><?php echo _t('팟캐스트');?></span></span><a href="<?php echo htmlspecialchars($entry['enclosure']);?>"><?php echo htmlspecialchars($entry['enclosure']);?></a></p>
<?php
		} else {
?>
													<p><span class="podcast-icon bullet"><span class="text"><?php echo _t('팟캐스트');?></span></span><a href="<?php echo htmlspecialchars($entry['enclosure']);?>"><?php echo htmlspecialchars($entry['enclosure']);?></a></p>
<?php
		}
	}
?>
													<?php echo $entry['description'];?>
												</div>

												<script type="text/javascript">
													//<![CDATA[
														Reader.selectedEntry = <?php echo escapeJSInAttribute($entry['id']);?>;
														Reader.setBlogTitle("<?php echo escapeJSInAttribute($entry['blog_title']);?>");
														Reader.doPostProcessingOnEntry();
													//]]>
												</script>

												<div id="entryFoot">
<?php
	if ($entry['tags']) {
?>
													<div id="entryTag">
														<span class="title"><?php echo htmlspecialchars(_t('태그'));?></span><span class="divider"> : </span><span class="tags"><?php echo htmlspecialchars($entry['tags']);?></span>
													</div>
<?php
	}
?>
													<div class="button-box">
														<a class="non-read-button button input-button" href="#void" onclick="Reader.markAsUnread(<?php echo $entry['id'];?>)"><span class="text"><?php echo _t('안 읽은 글로 표시');?></span></a>
													</div>
													<div class="shortcut-box">
													<ul>
														<li><kbd>A</kbd>, <kbd>H</kbd> - <?php echo _t('이전 글');?></li>
														<li><kbd>S</kbd>, <kbd>L</kbd> - <?php echo _t('다음 글');?></li>
<li><kbd>D</kbd> - <?php echo _t('새 창으로');?></li>
														<li><kbd>F</kbd> - <?php echo _t('안 읽은 글만보기');?></li>
														<li><kbd>G</kbd> - <?php echo _t('스크랩된 글 보기');?></li>
														<li><kbd>W</kbd> - <?php echo _t('현재글 스크랩');?></li>
														<li><kbd>T</kbd> - <?php echo _t('글 수집하기');?></li>
														<li><kbd>J</kbd> - <?php echo _t('위로 스크롤');?></li>
														<li class="last-shortcut"><kbd>K</kbd> - <?php echo _t('아래로 스크롤');?></li>
													</ul>
													</div>
												</div>
<?php
}

function printScript($filename, $obfuscate = true) {
	global $service, $hostURL, $blogURL, $serviceURL;
	if (!$file = @file_get_contents(ROOT . "/resources/script/$filename"))
		return '';
	$file = "<script type=\"text/javascript\">//<![CDATA[" . CRLF
		. $file;
//	if ($obfuscate) {
//	}
	return "$file //]]></script>";
}

function addOpenIDPannel( $comment, $prefix ) {
	if( !isActivePlugin( 'CL_OpenID' ) ) {
		return $comment;
	}
	global $service,$blogURL;
	$openid_identity = Acl::getIdentity('openid');
	$whatisopenid = '<a target="_blank" href="'._text('http://www.google.co.kr/search?q=OpenID&amp;lr=lang_ko').'"><span style="color:#ff6200">'._text('오픈아이디란?').'</span></a>';
	//$lastcomment = ' | <a href="#" onClick="recallLastComment([##_article_rep_id_##]); return false"><span style="color:#ff6200">'._text('마지막 댓글로 채우기').'</span></a>';
	$lastcomment = '';

	$openidOnlySettingNotice = '';
	if( Setting::getBlogSettingGlobal( 'AddCommentMode', '' ) == 'openid' ) {
		$openidOnlySettingNotice = "<b>"._text('오픈아이디로만 댓글을 남길 수 있습니다')."</b>";
	}

	$tag_login = '<a href="'.$blogURL.'/login/openid/guest?requestURI='.
			urlencode( $_SERVER["REQUEST_URI"] ).
			'"><span style="color:#ff6200">'._text('로그인').'</span></a>';

	$tag_logoff = '<a href="'.$blogURL.'/login/openid?action=logout&requestURI='.
			urlencode( $_SERVER["REQUEST_URI"] ).
			'"><span style="">'._text('로그아웃').'</span></a>';

	$pannel = '<div class="commentOuterPannel">'.CRLF;
	$openid_input = 'OPENID_TAG_NEEDED';

	$cookie_openid = '';
	if( !empty( $_COOKIE['openid'] ) ) {
		$cookie_openid = $_COOKIE['openid'];
	}
	if( $openidOnlySettingNotice || $openid_identity ) {
		$checked1 = 'checked="checked"'; $checked2 = '';
		$disabled1 = ''; $disabled2 = 'disabled="disabled"';
	} else {
		$checked1 = ''; $checked2 = 'checked="checked"';
		$disabled1 = 'disabled="disabled"'; $disabled2 = '';
	}

	$pannel_style = "style=\"width:100%; text-align:left\"";
	$radio_style  = "style=\"width:15px;vertical-align:text-bottom;height:15px;border:0px;margin:0px;padding:0px;\"";
	$label_style  = "style=\"display:inline;margin-top:0px;padding-left:0px;cursor:pointer\"";
	$openid_input_style = 'style="padding-left:21px;width:165px;background-image:url('.$service['path'].'/resources/image/icon_openid.gif'.');'.
					'background-repeat:no-repeat;background-position:0px center"';

	if( $openid_identity ) {
		$openid_input = '<span><a href="'.$openid_identity.'">'.OpenID::getDisplayName($openid_identity).'</a></span>'.CRLF;
		$openid_input .= '<input type="hidden" name="openid_identifier" id="openid_identifier_[##_article_rep_id_##]" value="'.htmlentities($openid_identity).'" />';
		$openid_input = _text('현재 로그인한 오픈아이디').' '.$openid_input;
		$_COOKIE['guestHomepage'] = $_SESSION['openid']['homepage'];
		$_COOKIE['guestName'] = $_SESSION['openid']['nickname'];
	} else {
		if( preg_match( '/.*?(<input[^>]+_(?:guest|rp)_input_homepage_[^>]+>).*/sm', $comment, $match ) ) {
			$openid_input = $match[1];
			$openid_input = str_replace( 'homepage_[##', 'openid_identifier_[##', $openid_input );
			$openid_input = str_replace( '[##_'.$prefix.'_input_homepage_##]', 'openid_identifier', $openid_input );
			$openid_input = preg_replace( '/value=(?:"|\')?(?:[^"\']+)(?:"|\')?/', 'value="'.$cookie_openid.'"', $openid_input );
			$openid_input = preg_replace( '/style=("|\')?([^"\']+)("|\')?/', '', $openid_input );
			$openid_input = preg_replace( '/(value=(?:"|\'))/', $openid_input_style.' $1', $openid_input );
		}
	}

	if( $disabled1 ) {
		$openid_input = preg_replace( '/(name=(?:"|\'))/', $disabled1.' $1', $openid_input );
	}
	if( $disabled2 ) {
		$comment = preg_replace( "/(.*)(<input)((?:[^>]+)name_\\[##_article_rep_id_##\\](?:[^>]+)>(?:.*))/sm", "$1$2 $disabled2 $3", $comment );
		$comment = preg_replace( "/(.*)(<input)((?:[^>]+)password_\\[##_article_rep_id_##\\](?:[^>]+)>(?:.*))/sm", "$1$2 $disabled2 $3", $comment );
		$comment = preg_replace( "/(.*)(<input)((?:[^>]+)\\[##_{$prefix}_input_name_##\\](?:[^>]+)>(?:.*))/sm", "$1$2 $disabled2 $3", $comment );
		$comment = preg_replace( "/(.*)(<input)((?:[^>]+)\\[##_{$prefix}_input_password_##\\](?:[^>]+)>(?:.*))/sm", "$1$2 $disabled2 $3", $comment );
	}

	$pannel .= '<div class="commentTypeOpenid" '.$pannel_style.'>'.
		'<input class="commentTypeCheckbox" '.$checked1.' type="radio" '.CRLF.
			$radio_style.CRLF.
			'id="comment_type_[##_article_rep_id_##]_openid" '.CRLF.
			'name="comment_type" value="openid" '.CRLF.
			'onclick="this.form.[##_'.$prefix.'_input_name_##].disabled=this.form.[##_'.$prefix.'_input_password_##].disabled=true;this.form.openid_identifier.disabled=false;this.form.openid_identifier.disabled=false;"'.CRLF.
			'/> '.CRLF.
		'<label for="comment_type_[##_article_rep_id_##]_openid" '.$label_style.'>'.
		_text('오픈아이디로 글쓰기').
		'</label> <span>['.($openid_identity ? $tag_logoff:$tag_login).']['.$whatisopenid.$lastcomment.']</span></div>'.CRLF;
	/* Opera browser does not work with single 'this.form.openid_identifier.disabled=false;', is it a bug? */

	$pannel .= '<div style="padding:5px 0 5px 0px;width:100%;">'.$openid_input.'</div>'.CRLF;

	$pannel .= '<div class="commentTypeNamepassword" '.$pannel_style.' >'.CRLF.
		'<input class="commentTypeCheckbox" '.$checked2.' type="radio" '.CRLF.
			$radio_style.CRLF.
			'id="comment_type_[##_article_rep_id_##]_idpwd" '.CRLF.
			'name="comment_type" value="idpwd" '.CRLF.
			'onclick="this.form.[##_'.$prefix.'_input_name_##].disabled=this.form.[##_'.$prefix.'_input_password_##].disabled=false;this.form.openid_identifier.disabled=true;this.form.openid_identifier.disabled=true;"'.CRLF.
			'/> '.CRLF.
		'<label for="comment_type_[##_article_rep_id_##]_idpwd" '.$label_style.'>'.
		_text('이름/비밀번호로 글쓰기').'</label> '.$openidOnlySettingNotice.'</div>'.CRLF;
	$comment = $pannel.$comment."</div>";
	return $comment;
}

function getTrackbackRDFView($blogid, $info) {
	$buf = new OutputWriter();
    $buf->buffer('<!--'.CRLF);
	$buf->buffer('<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'.CRLF);
    $buf->buffer('      xmlns:dc="http://purl.org/dc/elements/1.1/"'.CRLF);
    $buf->buffer('        xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">'.CRLF);
    $buf->buffer('<rdf:Description'.CRLF);
    $buf->buffer('   rdf:about="'.$info['permalink'].'"'.CRLF);
    $buf->buffer('   dc:identifier="'.$info['permalink'].'"'.CRLF);
    $buf->buffer('   dc:title="'.$info['title'].'"'.CRLF);
    $buf->buffer('   trackback:ping="'.$info['trackbackURL'].'" />'.CRLF);
    $buf->buffer('</rdf:RDF>'.CRLF);
	$buf->buffer('-->'.CRLF);
	return $buf->_buffer;
}
?>
