<?php 
function printHtmlHeader($title = '') {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
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

function dress($tag, $value, & $contents) {
	if (eregi("\[##_{$tag}_##\]", $contents, $temp)) {
		$contents = str_replace("[##_{$tag}_##]", $value, $contents);
		return true;
	} else {
		return false;
	}	
}

function getUpperView($paging) {
	global $service, $blogURL;
	ob_start();
?>
	<!--
		<?php echo TATTERTOOLS_NAME." ".TATTERTOOLS_VERSION.CRLF;?>
		
		Homepage: <?php echo TATTERTOOLS_HOMEPAGE.CRLF;?>
		<?php echo TATTERTOOLS_COPYRIGHT.CRLF;?>
	-->
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo $service['path'];?>";
			var blogURL = "<?php echo $blogURL;?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/EAF.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/common.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/gallery.js" ></script>
<?php 
	if (doesHaveOwnership()) {
?>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/owner.js" ></script>
<?php 
	}
?>
	<script type="text/javascript">
		//<![CDATA[
			function processShortcut(event) {
				if (isIE)
				{
					event = window.event;
					event.target = event.srcElement;
				}
			
				if (event.altKey || event.ctrlKey)
					return;
				switch (event.target.nodeName) {
					case "INPUT":
					case "SELECT":
					case "TEXTAREA":
						return;
				}
				switch (event.keyCode) {
					case 81: //Q
						window.location = "<?php echo $blogURL;?>/owner";
						break;
					case 82: //R
						window.location = "<?php echo $blogURL;?>/owner/reader";
						break;
					case 84: //T
						window.location = "<?php echo $blogURL;?>/owner/reader/?forceRefresh";
						break;
<?php 
	if (isset($paging['prev'])) {
?>
					case 65: //A
						window.location = "<?php echo escapeJSInCData("{$paging['url']}{$paging['prefix']}{$paging['prev']}{$paging['postfix']}");?>";
						break;
<?php 
	}
	if (isset($paging['next'])) {
?>
					case 83: //S
						window.location = "<?php echo escapeJSInCData("{$paging['url']}{$paging['prefix']}{$paging['next']}{$paging['postfix']}");?>";
						break;
<?php 
	}
?>
					case 90: //Z
						window.location = "#recentEntries";
						break;
					case 88: //X
						window.location = "#recentComments";
						break;
					case 67: //C
						window.location = "#recentTrackback";
						break;
				}
			}
			document.onkeydown = processShortcut;
			
			function addComment(caller, entryId) {
				var oForm = findFormObject(caller);
				if (!oForm)
					return false;
				var request = new HTTPRequest("POST", oForm.action);
				request.onSuccess = function () {
					document.getElementById("entry" + entryId + "Comment").innerHTML = this.getText("/response/commentBlock");
					if(document.getElementById("recentComments"))
						document.getElementById("recentComments").innerHTML = this.getText("/response/recentCommentBlock");
					if(document.getElementById("commentCount" + entryId))
						document.getElementById("commentCount" + entryId).innerHTML = this.getText("/response/commentView");
					if(document.getElementById("commentCountOnRecentEntries" + entryId))
						document.getElementById("commentCountOnRecentEntries" + entryId).innerHTML = this.getText("/response/commentCount");
				}
				request.onError = function() {
					alert(this.getText("/response/description"));
				}
				var queryString = "key=<?php echo md5(filemtime(ROOT . '/config.php'));?>";
				for (i=0; i<oForm.elements.length; i++) {
					if(oForm.elements[i].name == "name") {
						queryString += "&name_" + entryId +"=" + encodeURIComponent(oForm["name"].value);
					} else if(oForm.elements[i].name == "password") {
						queryString += "&password_" + entryId +"=" + encodeURIComponent(oForm["password"].value);
					} else if(oForm.elements[i].name == "email") {
						queryString += "&email_" + entryId +"=" + encodeURIComponent(oForm["email"].value);
					} else if(oForm.elements[i].name == "homepage") {
						queryString += "&homepage_" + entryId +"=" + encodeURIComponent(oForm["homepage"].value);
					} else if(oForm.elements[i].name == "secret") {
						if (oForm.elements[i].checked) {
							queryString += "&secret_" + entryId +"=1";
						}
					} else if(oForm.elements[i].name == "comment") {
						queryString += "&comment_" + entryId +"=" + encodeURIComponent(oForm["comment"].value);
					} else {
						if(oForm.elements[i].type == "radio") {
							queryString += "&" + oForm.elements[i].name + "_" + entryId +"=" + oForm.elements[i].value;
						} else if(oForm.elements[i].type == "checkbox") {
							queryString += "&" + oForm.elements[i].name + "_" + entryId +"=1";
						} else if(oForm.elements[i].name != '') {
							queryString += "&" + oForm.elements[i].name + "_" + entryId +"=" + encodeURIComponent(oForm.elements[i].value);
						}
					}
				}
				request.send(queryString);
			}

			var openWindow='';

			function alignCenter(win,width,height) {
				win.moveTo(screen.width/2-width/2,screen.height/2-height/2);
			}	
			
			function deleteComment(id) {
				width = 450;
				height = 400;
				if(openWindow != '') openWindow.close();
				openWindow = window.open("<?php echo $blogURL;?>/comment/delete/" + id, "tatter", "width="+width+",height="+height+",location=0,menubar=0,resizable=0,scrollbars=0,status=0,toolbar=0");
				openWindow.focus();
				alignCenter(openWindow,width,height);
			}
			
			function commentComment(parent) {	
				width = 450;
				height = 380;
				if(openWindow != '') openWindow.close();
				openWindow = window.open("<?php echo $blogURL;?>/comment/comment/" + parent, "tatter", "width="+width+",height="+height+",location=0,menubar=0,resizable=0,scrollbars=0,status=0,toolbar=0");
				openWindow.focus();
				alignCenter(openWindow,width,height);
			}
			
			function editEntry(parent,child) {	
				width =  825;
				height = 550;
				if(openWindow != '') openWindow.close();
				openWindow = window.open("<?php echo $blogURL;?>/owner/entry/edit/" + parent + "?popupEditor&returnURL=" + child,"tatter", "width="+width+",height="+height+",location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0");
				openWindow.focus();
				alignCenter(openWindow,width,height);
			}
			
			function guestbookComment(parent) {	
				width = 450;
				height = 360;
				if(openWindow != '') openWindow.close();
				openWindow = window.open("<?php echo $blogURL;?>/comment/comment/" + parent, "tatter", "width="+width+",height="+height+",location=0,menubar=0,resizable=0,scrollbars=0,status=0,toolbar=0");
				openWindow.focus();
				alignCenter(openWindow,width,height);
			}
			
			function sendTrackback(id) {
				width = 700;
				height = 500;
				if(openWindow != '') openWindow.close();
				openWindow = window.open("<?php echo $blogURL;?>/trackback/send/" + id, "tatter", "width=580,height=400,location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0");
				openWindow.focus();
				alignCenter(openWindow,width,height);
			}

			function copyUrl(url){		
				if(isIE) {
					window.clipboardData.setData('Text',url);
					window.alert("<?php echo _text('엮인글 주소가 복사되었습니다.');?>");
				}
			}
			
			
			function deleteTrackback(id,entryId) {
<?php 
	if (doesHaveOwnership()) {
?> 
				if (!confirm("<?php echo _text('선택된 글걸기를 삭제합니다. 계속 하시겠습니까?');?>"))
					return;

				var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/trackback/delete/" + id);
				request.onSuccess = function() {
					document.getElementById('entry'+entryId+'Trackback').innerHTML= this.getText("/response/result");
				}
				request.onError = function() {
					alert('<?php echo _text('실패했습니다.');?>');
				}
				request.send();
<?php 
	} else {
?>
				alert('<?php echo _text('실패했습니다.');?>');
<?php 
	}
?>
			}
<?php 
	if (doesHaveOwnership()) {
?>
			function changeVisibility(id, visibility) {
				var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/entry/visibility/" + id + "?visibility=" + visibility);
				request.onSuccess = function() {
					window.location.reload();
				}
				request.send();
			}
			
			function deleteEntry(id) {
				if (!confirm("<?php echo _text('이 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?');?>"))
					return;
				var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/entry/delete/" + id);
				request.onSuccess = function() {
					window.location.reload();
				}
				request.send();
			}	
<?php 
	}
?>
			function reloadEntry(id) {
				var password = document.getElementById("entry" + id + "password");
				if (!password)
					return;
				document.cookie = "GUEST_PASSWORD=" + escape(password.value) + ";path=<?php echo $blogURL;?>";

				window.location.href = window.location.href;				
			}
		//]]>
	</script>
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
	ob_start();
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
}

function getTrackbacksView($entryId, & $skin) {
	global $suri, $defaultURL, $skinSetting;
	$trackbacksView = '';
	foreach (getTrackbacks($entryId) as $trackback) {
		$trackbackView = "<a id=\"trackback{$trackback['id']}\"></a>" . $skin->trackback;
		dress('tb_rep_title', htmlspecialchars($trackback['subject']), $trackbackView);
		dress('tb_rep_site', htmlspecialchars($trackback['site']), $trackbackView);
		dress('tb_rep_url', htmlspecialchars($trackback['url']), $trackbackView);
		dress('tb_rep_desc', htmlspecialchars($trackback['excerpt']), $trackbackView);
		dress('tb_rep_onclick_delete', "deleteTrackback({$trackback['id']}, $entryId)", $trackbackView);
		dress('tb_rep_date', fireEvent('ViewTrackbackDate', Timestamp::format5($trackback['written'])), $trackbackView);
		$trackbacksView .= $trackbackView;
	}
	if ($skinSetting['expandTrackback'] == 1 || (($suri['url'] != $blogURL.'/index.php' && $suri['url'] != $service['path'].'/index.php') && ($suri['directive'] == '/' || $suri['directive'] == '/entry') && $suri['value'] != '')) {
		$style = 'block';
	} else {
		$style = 'none';
	}
	$trackbacksView = "<div id=\"entry{$entryId}Trackback\" style=\"display:$style\">" . str_replace('[##_tb_rep_##]', $trackbacksView, $skin->trackbacks) . '</div>';
	dress('tb_address', "<span onclick=\"copyUrl('$defaultURL/trackback/$entryId')\">$defaultURL/trackback/$entryId</span>", $trackbacksView);
	return $trackbacksView;
}

function getCommentView($entryId, & $skin) {
	global $database, $blogURL, $service, $owner, $suri, $paging, $blog;
	//if ($entryId <= 0)
	//	return getGuestCommentView($entryId, $skin);
	$authorized = doesHaveOwnership();
	$skinValue = getSkinSetting($owner);
	$blogSetting = getBlogSetting($owner);
	if ($entryId > 0) {
		$prefix1 = 'rp';
		$prefix2 = 'comment';
		$isComment = true;
		$SubItem = 'commentSubItem';
	} else {
		$prefix1 = 'guest';
		$prefix2 = 'guestbook';
		$isComment = false;
		$SubItem = 'guestSubItem';
	}
	$commentView = "<form method=\"post\" action=\"$blogURL/comment/add/$entryId\" onsubmit=\"return false\" style=\"margin: 0\">" . ($isComment ? $skin->comment : $skin->guest) . '</form>';
	$commentItemsView = '';
	if ($entryId == 0) {
		list($comments, $paging) = getCommentsWithPagingForGuestbook($owner, $suri['page'], $skinValue['commentsOnGuestbook']);
		foreach ($comments as $key => $value) {
			if ($value['secret'] == 1 && !$authorized) {
				$comments[$key]['name'] = '';
				$comments[$key]['homepage'] = '';
				$comments[$key]['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
			}
		}
	} else {
		$comments = getComments($entryId);
	}
	
	$slash = ($commentSubItem['homepage']{strlen($commentSubItem['homepage']) - 1} == '/' ? '' : '/');
	
	// 블로그 아이콘의 onError 이벤트 자바스크립트 생성.
	if (file_exists(ROOT."/skin/{$skinSetting['skin']}/image/icon_guest_default.png")) {
		$onErrorJs = "this.src='{$service['path']}/skin/{$skinSetting['skin']}/image/icon_guest_default.png'";
	} else if (file_exists(ROOT."/image/icon_guest_default.png")) {
		$onErrorJs = "this.src='{$service['path']}/image/icon_guest_default.png'";
	} else {
		$onErrorJs = "this.parentNode.removeChild(this)";
	}
	
	// 비밀 모드의 블로그 아이콘 onError 이벤트 자바스크립트 생성.
	if (file_exists(ROOT."/skin/{$skinSetting['skin']}/image/icon_guest_secret.png")) {
		$onErrorJsSecret = "this.src='{$service['path']}/skin/{$skinSetting['skin']}/image/icon_guest_secret.png'";
	} else if (file_exists(ROOT."/image/icon_guest_secret.png")) {
		$onErrorJsSecret = "this.src='{$service['path']}/image/icon_guest_secret.png'";
	} else {
		$onErrorJsSecret = "this.parentNode.removeChild(this)";
	}
	
	// 관리자가 로그인한 상태라면 비밀 모드는 필요없다.
	if ($authorized)
		$onErrorJsSecret = $onErrorJs;
	
	$blogIconSize = getUserSetting('blogIconSize', 0);
	foreach ($comments as $commentItem) {
		$commentItemView = ($isComment ? $skin->commentItem : $skin->guestItem);
		if (!dress($prefix1 . '_rep_id', "comment{$commentItem['id']}", $commentItemView))
			$commentItemView = "<a id=\"comment{$commentItem['id']}\"></a>" . $commentItemView;
		dress($prefix1 . '_rep_id',"comment{$commentItem['id']}", $commentItemView);
		$commentSubItemsView = '';
		foreach (getCommentComments($commentItem['id']) as $commentSubItem) {
			$commentSubItemView = ($isComment ? $skin->commentSubItem : $skin->guestSubItem);
			if (!dress($prefix1 . '_rep_id', "comment{$commentSubItem['id']}", $commentSubItemView))
				$commentSubItemView = "<a id=\"comment{$commentSubItem['id']}\"></a>" . $commentSubItemView;
			dress($prefix1 . '_rep_id',"comment{$commentSubItem['id']}", $commentSubItemView);
			if (empty($commentSubItem['homepage'])) {
				dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), htmlspecialchars($commentSubItem['name']), $commentSubItem), $commentSubItemView);
			} else {
				dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), '<a href="' . htmlspecialchars(addProtocolSense($commentSubItem['homepage'])) . '" onclick="return openLinkInNewWindow(this)">' . htmlspecialchars($commentSubItem['name']) . '</a>', $commentSubItem), $commentSubItemView);
			}
			if ($blogIconSize != '0') {
				// 비밀 댓글인 경우, 관리자 로그인 상태가 아니면 블로그 아이콘을 익명 아이콘으로 바꿔준다.
				$onErrorScript = $commentSubItem['secret'] == 1 ? $onErrorJsSecret : $onErrorJs;
				dress($prefix1 . '_rep_icon', fireEvent(($isComment ? 'ViewCommentIcon' : 'ViewGuestCommentIcon'), "<img class=\"tt-icon\" src=\"{$commentSubItem['homepage']}{$slash}index.gif\" width=\"$blogIconSize\" height=\"$blogIconSize\" onerror=\"{$onErrorScript}\" />", $commentSubItem), $commentSubItemView);
			}
			dress($prefix1 . '_rep_desc', fireEvent(($isComment ? 'ViewCommentContent' : 'ViewGuestCommentContent'), nl2br(addLinkSense(htmlspecialchars($commentSubItem['comment']), ' onclick="return openLinkInNewWindow(this)"')), $commentSubItem), $commentSubItemView);
			dress($prefix1 . '_rep_date', fireEvent(($isComment ? 'ViewCommentDate' : 'ViewGuestCommentDate'), Timestamp::format5($commentSubItem['written'])), $commentSubItemView);
			dress($prefix1 . '_rep_link',"$blogURL/{$entryId}#comment{$commentSubItem['id']}", $commentSubItemView);
			dress($prefix1 . '_rep_onclick_delete', "deleteComment({$commentSubItem['id']}); return false;", $commentSubItemView);
			$rp_class = 'tt-guest-'.$prefix2;
			if ($owner == $commentSubItem['replier'])
				$rp_class = 'tt-admin-'.$prefix2;
			else if ($commentSubItem['secret'] == 1)
				$rp_class = 'tt-secret-'.$prefix2;
			dress($prefix1 . '_rep_class', $rp_class, $commentSubItemView);
			$commentSubItemsView .= $commentSubItemView;
		}
		dress(($isComment ? 'rp2_rep' : 'guest_reply_rep'), $commentSubItemsView, $commentItemView);
		if (empty($commentItem['homepage'])) {
			dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), htmlspecialchars($commentItem['name']), $commentItem), $commentItemView);
		} else {
			dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), '<a href="' . htmlspecialchars(addProtocolSense($commentItem['homepage'])) . '" onclick="return openLinkInNewWindow(this)">' . htmlspecialchars($commentItem['name']) . '</a>', $commentItem), $commentItemView);
		}
		if ($blogIconSize != '0') {
			// 비밀 댓글인 경우, 관리자 로그인 상태가 아니면 블로그 아이콘을 익명 아이콘으로 바꿔준다.
			$onErrorScript = $commentItem['secret'] == 1 ? $onErrorJsSecret : $onErrorJs;
			dress($prefix1 . '_rep_icon', fireEvent(($isComment ? 'ViewCommentIcon' : 'ViewGuestCommentIcon'), "<img class=\"tt-icon\" src=\"{$commentItem['homepage']}{$slash}index.gif\" width=\"$blogIconSize\" height=\"$blogIconSize\" onerror=\"{$onErrorScript}\" />", $commentItem), $commentItemView);
		}
		dress($prefix1 . '_rep_desc', fireEvent(($isComment ? 'ViewCommentContent' : 'ViewGuestCommentContent'), nl2br(addLinkSense(htmlspecialchars($commentItem['comment']), ' onclick="return openLinkInNewWindow(this)"')), $commentItem), $commentItemView);
		dress($prefix1 . '_rep_date', fireEvent(($isComment ? 'ViewCommentDate' : 'ViewGuestCommentDate'), Timestamp::format5($commentItem['written'])), $commentItemView);
		if ($prefix1 == 'guest' && $authorized != true && $blogSetting['allowWriteDoubleCommentOnGuestbook'] == 0) {
			$doubleCommentPermissionScript = 'alert(\'' . _text('댓글을 사용할 수 없습니다.') . '\'); return false;';
		} else {
			$doubleCommentPermissionScript = '';
		}
		dress($prefix1 . '_rep_onclick_reply', $doubleCommentPermissionScript . "commentComment({$commentItem['id']}); return false", $commentItemView);
		dress($prefix1 . '_rep_onclick_delete', "deleteComment({$commentItem['id']});return false", $commentItemView);
		dress($prefix1 . '_rep_link', "$blogURL/{$entryId}#comment{$commentItem['id']}", $commentItemView);
		$rp_class = 'tt-guest-'.$prefix2;
		if ($owner == $commentItem['replier'])
			$rp_class = 'tt-admin-'.$prefix2;
		else if ($commentItem['secret'] == 1)
			$rp_class = 'tt-secret-'.$prefix2;
		dress($prefix1 . '_rep_class', $rp_class, $commentItemView);
		$commentItemsView .= $commentItemView;
	}
	dress($prefix1 . '_rep', $commentItemsView, $commentView);
	
	$acceptComment = fetchQueryCell("SELECT `acceptComment` FROM `{$database['prefix']}Entries` WHERE `id` = $entryId");
	
	if (doesHaveOwnership() || ($isComment && $acceptComment == 1) || ($prefix2 == "guestbook")) {
		if ($isComment) {
			$commentRrevView = $commentView;
			$commentView = $skin->commentForm;
		}
		
		if (!doesHaveOwnership()) {
			$commentMemberView = ($isComment ? $skin->commentMember : $skin->guestMember);
			if (!doesHaveMembership()) {
				$commentGuestView = ($isComment ? $skin->commentGuest : $skin->guestGuest);
				dress($prefix1 . '_input_name', 'name', $commentGuestView);
				dress($prefix1 . '_input_password', 'password', $commentGuestView);
				dress($prefix1 . '_input_homepage', 'homepage', $commentGuestView);
				dress($prefix1 . '_input_email', 'email', $commentGuestView);
				if (!empty($_POST["name_$entryId"]))
					$guestName = htmlspecialchars($_POST["name_$entryId"]);
				else if (!empty($_COOKIE['guestName']))
					$guestName = htmlspecialchars($_COOKIE['guestName']);
				else
					$guestName = '';
				dress('guest_name', $guestName, $commentGuestView);
				if (!empty($_POST["email_$entryId"]))
					$guestEmail = htmlspecialchars($_POST["email_$entryId"]);
				else if (!empty($_COOKIE['guestEmail']))
					$guestEmail = htmlspecialchars($_COOKIE['guestEmail']);
				else
					$guestEmail = '';
				dress('guest_email', $guestEmail, $commentGuestView);
				if (!empty($_POST["homepage_$entryId"]) && $_POST["homepage_$entryId"] != 'http://') {
					if (strpos($_POST["homepage_$entryId"], 'http://') === 0)
						$guestHomepage = htmlspecialchars($_POST["homepage_$entryId"]);
					else
						$guestHomepage = 'http://' . htmlspecialchars($_POST["homepage_$entryId"]);
				} else if (!empty($_COOKIE['guestHomepage']))
					$guestHomepage = htmlspecialchars($_COOKIE['guestHomepage']);
				else
					$guestHomepage = 'http://';
				dress('guest_homepage', $guestHomepage, $commentGuestView);
				dress($prefix1 . ($isComment ? '_guest' : '_form'), $commentGuestView, $commentMemberView);
			}
			dress($prefix1 . '_input_is_secret', 'secret', $commentMemberView);
			dress($prefix1 . '_member', $commentMemberView, $commentView);
		}
		
		dress($prefix1 . '_input_comment', 'comment', $commentView);
		dress($prefix1 . '_onclick_submit', "addComment(this, $entryId); return false", $commentView);
		dress($prefix1 . '_textarea_body', 'comment', $commentView);
		dress($prefix1 . '_textarea_body_value', '', $commentView);
		
		if ($isComment) {
			dress($prefix1 . '_form', $commentView, $commentRrevView);
			$commentView = $commentRrevView;
		}
	}
	
	return $commentView;
}

function getGuestCommentView($entryId, & $skin) {
	global $blogURL, $owner, $suri, $paging, $blog, $skinSetting;
	$authorized = doesHaveOwnership();
	if ($entryId > 0) {
		$prefix1 = 'rp';
		$prefix2 = 'comment';
		$isComment = true;
		$SubItem = 'commentSubItem';
	} else {
		$prefix1 = 'guest';
		$prefix2 = 'guest';
		$isComment = false;
		$SubItem = 'guestSubItem';
	}
	$commentView = "<form method=\"post\" action=\"$blogURL/comment/add/$entryId\" onsubmit=\"return false\" style=\"margin: 0\">" . ($isComment ? $skin->comment : $skin->guest) . '</form>';
	$commentItemsView = '';
	if ($entryId == 0) {
		list($comments, $paging) = getCommentsWithPagingForGuestbook($owner, $suri['page'], $skinSetting['commentsOnGuestbook']);
		foreach ($comments as $key => $value) {
			if ($value['secret'] == 1 && !$authorized) {
				$comments[$key]['name'] = '';
				$comments[$key]['homepage'] = '';
				$comments[$key]['comment'] = _t('관리자만 볼 수 있는 댓글입니다');
			}
		}
	} else {
		$comments = getComments($entryId);
	}
	foreach ($comments as $commentItem) {
		$commentItemView = "<a id=\"comment{$commentItem['id']}\"></a>" . ($isComment ? $skin->commentItem : $skin->guestItem);
		$commentSubItemsView = '';
		foreach (getCommentComments($commentItem['id']) as $commentSubItem) {
			$commentSubItemView = "<a id=\"comment{$commentSubItem['id']}\"></a>" . ($isComment ? $skin->commentSubItem : $skin->guestSubItem);
			if (empty($commentSubItem['homepage']))
				dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), htmlspecialchars($commentSubItem['name']), $commentSubItem), $commentSubItemView);
			else
				dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), '<a href="' . htmlspecialchars(addProtocolSense($commentSubItem['homepage'])) . '" onclick="return openLinkInNewWindow(this)">' . htmlspecialchars($commentSubItem['name']) . '</a>', $commentSubItem), $commentSubItemView);
			dress($prefix1 . '_rep_desc', fireEvent(($isComment ? 'ViewCommentContent' : 'ViewGuestCommentContent'), ($commentSubItem['secret'] && $authorized ? '<div class="hiddenComment" style="font-weight: bold; color: #e11">'._t('비밀 댓글').' &gt;&gt;</div>' : '').nl2br(addLinkSense(htmlspecialchars($commentSubItem['comment']), ' onclick="return openLinkInNewWindow(this)"')), $commentSubItem), $commentSubItemView);
			dress($prefix1 . '_rep_date', Timestamp::format5($commentSubItem['written']), $commentSubItemView);
			dress($prefix1 . '_rep_link',"$blogURL/{$entryId}#comment{$commentSubItem['id']}", $commentSubItemView);
			dress($prefix1 . '_rep_onclick_delete', "deleteComment({$commentSubItem['id']});return false", $commentSubItemView);
			$commentSubItemsView .= $commentSubItemView;
		}
		dress(($isComment ? 'rp2_rep' : 'guest_reply_rep'), $commentSubItemsView, $commentItemView);
		if (empty($commentItem['homepage']))
			dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), htmlspecialchars($commentItem['name']), $commentItem), $commentItemView);
		else
			dress($prefix1 . '_rep_name', fireEvent(($isComment ? 'ViewCommenter' : 'ViewGuestCommenter'), '<a href="' . htmlspecialchars(addProtocolSense($commentItem['homepage'])) . '" onclick="return openLinkInNewWindow(this)">' . htmlspecialchars($commentItem['name']) . '</a>', $commentItem), $commentItemView);
		dress($prefix1 . '_rep_desc', fireEvent(($isComment ? 'ViewCommentContent' : 'ViewGuestCommentContent'), ($commentItem['secret'] && $authorized ? '<div class="hiddenComment" style="font-weight: bold; color: #e11">'._t('비밀 댓글').' &gt;&gt;</div>' : '').nl2br(addLinkSense(htmlspecialchars($commentItem['comment']), ' onclick="return openLinkInNewWindow(this)"')), $commentItem), $commentItemView);
		dress($prefix1 . '_rep_date', Timestamp::format5($commentItem['written']), $commentItemView);
		if ($prefix1 == 'guest' && $authorized != true && $blog['allowWriteDoubleCommentOnGuestbook'] == 0) {
			$doubleCommentPermissionScript = 'alert(\'' . _t('댓글을 사용할 수 없습니다') . '\');return false;';
		} else {
			$doubleCommentPermissionScript = '';
		}
		dress($prefix1 . '_rep_onclick_reply', $doubleCommentPermissionScript . "commentComment({$commentItem['id']});return false", $commentItemView);
		dress($prefix1 . '_rep_onclick_delete', "deleteComment({$commentItem['id']});return false", $commentItemView);
		dress($prefix1 . '_rep_link', "$blogURL/{$entryId}#comment{$commentItem['id']}", $commentItemView);
		$commentItemsView .= $commentItemView;
	}
	dress($prefix1 . '_rep', $commentItemsView, $commentView);
	if (!doesHaveOwnership()) {
		$commentMemberView = ($isComment ? $skin->commentMember : $skin->guestMember);
		if (!doesHaveMembership()) {
			$commentGuestView = ($isComment ? $skin->commentGuest : $skin->guestGuest);
			dress($prefix1 . '_input_name', 'name', $commentGuestView);
			dress($prefix1 . '_input_password', 'password', $commentGuestView);
			dress($prefix1 . '_input_homepage', 'homepage', $commentGuestView);
			if (!empty($_POST["name_$entryId"]))
				$guestName = htmlspecialchars($_POST["name_$entryId"]);
			else if (!empty($_COOKIE['guestName']))
				$guestName = htmlspecialchars($_COOKIE['guestName']);
			else
				$guestName = '';
			dress('guest_name', $guestName, $commentGuestView);
			if (!empty($_POST["homepage_$entryId"]) && $_POST["homepage_$entryId"] != 'http://') {
				if (strpos($_POST["homepage_$entryId"], 'http://') === 0)
					$guestHomepage = htmlspecialchars($_POST["homepage_$entryId"]);
				else
					$guestHomepage = 'http://' . htmlspecialchars($_POST["homepage_$entryId"]);
			} else if (!empty($_COOKIE['guestHomepage']))
				$guestHomepage = htmlspecialchars($_COOKIE['guestHomepage']);
			else
				$guestHomepage = 'http://';
			dress('guest_homepage', $guestHomepage, $commentGuestView);
			dress($prefix1 . ($isComment ? '_guest' : '_form'), $commentGuestView, $commentMemberView);
		}
		dress($prefix1 . '_input_is_secret', 'secret', $commentMemberView);
		dress($prefix1 . '_member', $commentMemberView, $commentView);
	}
	dress($prefix1 . '_input_comment', 'comment', $commentView);
	dress($prefix1 . '_onclick_submit', "addComment(this, $entryId);return false", $commentView);
	dress($prefix1 . '_textarea_body', 'comment', $commentView);
	dress($prefix1 . '_textarea_body_value', '', $commentView);
	return $commentView;
}

function getCategoriesView($totalPosts, $categories, $selected, $xhtml = false) {
	global $blogURL, $owner;
	$tree = array('id' => 0, 'label' => getCategoryNameById($owner, 0), 'value' => $totalPosts, 'link' => "$blogURL/category", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		foreach ($category1['children'] as $category2) {
			array_push($children, array('id' => $category2['id'], 'label' => $category2['name'], 'value' => (doesHaveOwnership() ? $category2['entriesInLogin'] : $category2['entries']), 'link' => "$blogURL/category/" . encodeURL($category2['label']), 'children' => array()));
		}
		array_push($tree['children'], array('id' => $category1['id'], 'label' => $category1['name'], 'value' => (doesHaveOwnership() ? $category1['entriesInLogin'] : $category1['entries']), 'link' => "$blogURL/category/" . encodeURL($category1['label']), 'children' => $children));
	}
	ob_start();
	printTreeView($tree, $selected, $xhtml);
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getCategoriesViewInOwner($totalPosts, $categories, $selected) {
	global $blogURL, $owner;
	$tree = array('id' => 0, 'label' => getCategoryNameById($owner, 0), 'value' => $totalPosts, 'link' => "$blogURL/owner/entry/category", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		foreach ($category1['children'] as $category2) {
			array_push($children, array('id' => $category2['id'], 'label' => $category2['name'], 'value' =>  $category2['entriesInLogin'], 'link' => "$blogURL/owner/entry/category/?id={$category2['id']}&entries={$category2['entries']}&priority={$category1['priority']}&name1=" . rawurlencode($category2['name']) . "&name2=" . rawurlencode($category2['name']), 'children' => array()));
		}
		array_push($tree['children'], array('id' => $category1['id'], 'label' => $category1['name'], 'value' => $category1['entriesInLogin'], 'link' => "$blogURL/owner/entry/category/?&id={$category1['id']}&entries={$category1['entries']}&priority={$category1['priority']}&name1=" . rawurlencode($category1['name']), 'children' => $children));
	}
	ob_start();
	printTreeView($tree, $selected);
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getCategoriesViewInSkinSetting($totalPosts, $categories, $selected) {
	global $owner;
	$tree = array('id' => 0, 'label' => getCategoryNameById($owner, 0), 'value' => $totalPosts, 'link' => "", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		foreach ($category1['children'] as $category2) {
			array_push($children, array('id' => $category2['id'], 'label' => $category2['name'], 'value' => (doesHaveOwnership() ? $category2['entriesInLogin'] : $category2['entries']), 'link' => "", 'children' => array()));
		}
		array_push($tree['children'], array('id' => $category1['id'], 'label' => $category1['name'], 'value' => (doesHaveOwnership() ? $category1['entriesInLogin'] : $category1['entries']), 'link' => "", 'children' => $children));
	}
	ob_start();
	printTreeView($tree, $selected);
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function printTreeView($tree, $selected, $xhtml=false) {
	$skin = getCategoriesSkin();
	if ($xhtml) {
		echo '<ul>';
		$isSelected = ($tree['id'] === $selected) ? ' class="selected"' : '';
		echo "<li$isSelected><a href=\"", htmlspecialchars($tree['link']), '">', htmlspecialchars($tree['label']), " <span class=\"c_cnt\">({$tree['value']})</span></a>";
		if (sizeof($tree['children']) > 0)
			echo '<ul>';
		foreach ($tree['children'] as $child) {
			$isSelected = ($child['id'] === $selected) ? ' class="selected"' : '';
			echo "<li$isSelected><a href=\"", htmlspecialchars($child['link']), '">', htmlspecialchars($child['label']), " <span class=\"c_cnt\">({$child['value']})</span></a>";
			if (sizeof($child['children']) > 0)
				echo '<ul>';
			foreach ($child['children'] as $leaf) {
				$isSelected = ($leaf['id'] === $selected) ? ' class="selected"' : '';
				echo "<li$isSelected><a href=\"", htmlspecialchars($leaf['link']), '">', htmlspecialchars($leaf['label']), " <span class=\"c_cnt\">({$leaf['value']})</span></a></li>";
			}
			if (sizeof($child['children']) > 0)
				echo '</ul>';
			echo '</li>';
		}
		if (sizeof($tree['children']) > 0)
			echo "</ul>";
		echo '</li></ul>';
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
						oChild.style.backgroundColor ='#'+skin['bgColorOnTree'];
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
	<table id="treeComponent" currentselectednode="<?php echo $selected;?>" cellpadding="0" cellspacing="0" style="width: 100%;"><tr>
	<td>
		<table id="category_0" name="treeNode" cellpadding="0" cellspacing="0"><tr>
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
		<table name="treeNode"  id="category_<?php echo $row['id'];?>" cellpadding="0" cellspacing="0"><tr>
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
				<table id="category_<?php echo $irow['id'];?>" name="treeNode" cellpadding="0" cellspacing="0"><tr>
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
<?php 
		if ($parentOfSelected) {
?>
	expandFolder(<?php echo $parentOfSelected;?>, true);
<?php 
		}
?>
	selectNode(<?php echo $selected;?>);
//]]>
</script>
<?php 
	}
}

function getArchivesView($archives, & $template) {
	global $blogURL;
	ob_start();
	foreach ($archives as $archive) {
		$view = "$template";
		dress('archive_rep_link', "$blogURL/archive/{$archive['period']}", $view);
		dress('archive_rep_date', fireEvent('ViewArchiveDate', getPeriodLabel($archive['period'])), $view);
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
<table id="tt-calendar" cellpadding="0" cellspacing="1" style="width: 100%; table-layout: fixed">
	<caption class="tt-cal-month cal_month">
		<a class="tt-prev-month" href="<?php echo $blogURL;?>/archive/<?php echo $previous;?>" title="<?php echo _text('1개월 앞의 달력을 보여줍니다.');?>">&laquo;</a>
		&nbsp;
		<a class="tt-current-month" href="<?php echo $blogURL;?>/archive/<?php echo $current;?>" title="<?php echo _text('현재 달의 달력을 보여줍니다.');?>"><?php echo $currentMonthStr;?></a>
		&nbsp;
		<a class="tt-next-month" href="<?php echo $blogURL;?>/archive/<?php echo $next;?>" title="<?php echo _text('1개월 뒤의 달력을 보여줍니다.');?>">&raquo;</a>
	</caption>
	<thead>
		<tr>
			<th class="tt-cal-cell tt-cal-sunday cal_week2"><?php echo fireEvent('ViewCalendarHeadWeekday', _text('일요일'));?></th>
			<th class="tt-cal-cell tt-cal-commonday cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('월요일'));?></th>
			<th class="tt-cal-cell tt-cal-commonday cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('화요일'));?></th>
			<th class="tt-cal-cell tt-cal-commonday cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('수요일'));?></th>
			<th class="tt-cal-cell tt-cal-commonday cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('목요일'));?></th>
			<th class="tt-cal-cell tt-cal-commonday cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('금요일'));?></th>
			<th class="tt-cal-cell tt-cal-satureday cal_week1"><?php echo fireEvent('ViewCalendarHeadWeekday',_text('토요일'));?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$day = 0;
	$totalDays = $firstWeekday + $lastDay;
	$lastWeek = ceil($totalDays / 7);
	
	for ($week=0; $week<$lastWeek; $week++) {
		// 주중에 현재 날짜가 포함되어 있으면 주를 현재 주 class(tt-current-week)를 부여한다.
		if (($today + $firstWeekday) >= $week * 7 && ($today + $firstWeekday) < ($week + 1) * 7) {
			echo '		<tr class="tt-cal-week tt-current-week">'.CRLF;
		} else {
			echo '		<tr class="tt-cal-week">'.CRLF;
		}
		
		for($weekday=0; $weekday<7; $weekday++) {
			$day++;
			$dayString = isset($calendar['days'][$day]) ? '<a class="tt-cal-click cal_click" href="'.$blogURL.'/archive/'.$current.($day > 9 ? $day : "0$day").'">'.$day.'</a>' : $day;
			
			// 일요일, 평일, 토요일별로 class를 부여한다.
			switch ($weekday) {
				case 0:
					$className = " tt-cal-sunday";
					break;
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
					$className = " tt-cal-commonday";
					break;
				case 6:
					$className = " tt-cal-satureday";
					break;
			}
			
			// 오늘에 현재 class(tt-current-day)를 부여한다.
			$className .= $day == $today ? " tt-current-day cal_day4" : " cal_day3";
			
			if ($week == 0) {
				if ($weekday < $firstWeekday) {
					$day--;
					// 달의 첫째날이 되기 전의 빈 칸.
					echo '			<td class="tt-cal-empty cal_day1">&nbsp;</td>'.CRLF;
				} else {
					echo '			<td class="tt-cal-cell'.$className.'">'.$dayString.'</td>'.CRLF;
				}
			} else if ($week == ($lastWeek - 1)) {
				if ($day <= $lastDay) {
					echo '			<td class="tt-cal-cell'.$className.'">'.$dayString.'</td>'.CRLF;
				} else {
					// 달의 마지막날을 넘어간 날짜 빈 칸.
					echo '			<td class="tt-cal-empty cal_day2">&nbsp;</td>'.CRLF;
				}
			} else {
				echo '			<td class="tt-cal-cell'.$className.'">'.$dayString.'</td>'.CRLF;
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

function getRecentEntriesView($entries, & $template) {
	global $blogURL, $skinSetting;
	ob_start();
	foreach ($entries as $entry) {
		$view = "$template";
		dress('rctps_rep_link', "$blogURL/{$entry['id']}", $view);
		dress('rctps_rep_title', htmlspecialchars(UTF8::lessenAsEm($entry['title'], $skinSetting['recentEntryLength'])), $view);
		dress('rctps_rep_rp_cnt', "<span id=\"commentCountOnRecentEntries{$entry['id']}\">".($entry['comments'] > 0 ? "{$entry['comments']}" : '').'</span>', $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getRecentCommentsView($comments, & $template) {
	global $blogURL, $skinSetting;
	ob_start();
	foreach ($comments as $comment) {
		$view = "$template";
		dress('rctrp_rep_link', "$blogURL/{$comment['entry']}#comment{$comment['id']}", $view);
		dress('rctrp_rep_desc', htmlspecialchars(UTF8::lessenAsEm($comment['comment'], $skinSetting['recentCommentLength'])), $view);
		dress('rctrp_rep_time', fireEvent('ViewRecentCommentDate', Timestamp::format2($comment['written'])), $view);
		dress('rctrp_rep_name', htmlspecialchars($comment['name']), $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getRecentTrackbacksView($trackbacks, & $template) {
	global $blogURL, $skinSetting;
	ob_start();
	foreach ($trackbacks as $trackback) {
		$view = "$template";
		dress('rcttb_rep_link', "$blogURL/{$trackback['entry']}#trackback{$trackback['id']}", $view);
		dress('rcttb_rep_desc', htmlspecialchars(UTF8::lessenAsEm($trackback['subject'], $skinSetting['recentTrackbackLength'])), $view);
		dress('rcttb_rep_time', fireEvent('ViewRecentTrackbackDate', Timestamp::format2($trackback['written'])), $view);
		dress('rcttb_rep_name', htmlspecialchars(UTF8::lessenAsEm($trackback['site'], $skinSetting['recentTrackbackLength'])), $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getLinksView($links, & $template) {
	global $blogURL, $skinSetting;
	ob_start();
	foreach ($links as $link) {
		$view = "$template";
		dress('link_url', htmlspecialchars($link['url']), $view);
		dress('link_site', fireEvent('ViewLink', htmlspecialchars(UTF8::lessenAsEm($link['name'], $skinSetting['linkLength']))), $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getRandomTagsView($tags, & $template) {
	global $blogURL;
	ob_start();
	list($maxTagFreq, $minTagFreq) = getTagFrequencyRange();
	foreach ($tags as $tag) {
		$view = $template;
		dress('tag_link', "$blogURL/tag/" . encodeURL($tag), $view);
		dress('tag_name', htmlspecialchars($tag), $view);
		dress('tag_class', "cloud" . getTagFrequency($tag, $maxTagFreq, $minTagFreq), $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getEntryContentView($owner, $id, $content, $keywords = array(), $type = 'Post', $useAbsolutePath = false, $bRssMode = false) {
	global $service;
	$path = ROOT . "/attach/$owner";
	$url = "{$service['path']}/attach/$owner";
	$view = bindAttachments($id, $path, $url, $content, $useAbsolutePath, $bRssMode);
	$view = bindKeywords($keywords, $view);
	$view = bindTags($id, $view);
	if (defined('__TATTERTOOLS_MOBILE__'))
		$view = stripHTML($view, array('a', 'abbr', 'acronym', 'address', 'b', 'blockquote', 'br', 'cite', 'code', 'dd', 'del', 'dfn', 'div', 'dl', 'dt', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins', 'kbd', 'li', 'ol', 'p', 'pre', 'q', 's', 'samp', 'span', 'strike', 'strong', 'sub', 'sup', 'u', 'ul', 'var'));
	if(!$useAbsolutePath)
		$view = avoidFlashBorder($view);
	return fireEvent('View' . $type . 'Content', $view, $id);
}

function printEntryContentView($owner, $id, $content, $keywords = array()) {
	print (getEntryContentView($owner, $id, $content, $keywords));
}

function bindTags($id, $content) {
	for ($no = 0; (($start = strpos($content, '[#M_')) !== false) && (($end = strpos($content, '_M#]', $start + 4)) !== false); $no++) {
		$prefix = substr($content, 0, $start);
		list($more, $less, $full) = explode('|', substr($content, $start + 4, $end - $start - 4), 3);
		$postfix = substr($content, $end + 4);
		$content = $prefix;
		if (defined('__TATTERTOOLS_MOBILE__')) {
			$content .= "<div>[$more | $less]<br />$full</div>";
		} else {
			$content .= "<p id=\"more{$id}_$no\" class=\"tt-more-before\"><span style=\"cursor: pointer;\" onclick=\"toggleMoreLess(this, '{$id}_$no','$more','$less'); return false;\">$more</span></p><div id=\"content{$id}_$no\" class=\"tt-more-content\" style=\"display: none;\">$full</div>";
			//$content .= "<div id=\"more{$id}_$no\" style=\"display:block\"><a href=\"#\" onclick=\"hideLayer('more{$id}_$no');showLayer('less{$id}_$no');return false\"></a></div>";
			//$content .= "<div id=\"less{$id}_$no\" style=\"display:none\"><a href=\"#\" onclick=\"showLayer('more{$id}_$no');hideLayer('less{$id}_$no');return false\"></a>$full</div>";
		}
		$content .= $postfix;
	}
	return $content;
}

function bindKeywords($keywords, $content) {
	global $blogURL;
	$flags = array();
	foreach ($keywords as $i => $keyword) {
		for ($offset = 0; ($start = strpos($content, $keyword, $offset)) !== false; $offset = $start + strlen($keyword)) {
			if (array_key_exists($start, $flags))
				continue;
			$flags[$start] = $i;
		}
	}
	ksort($flags);
	
	$view = '';
	$offset = 0;
	foreach ($flags as $start => $flag) {
		if ($start < $offset)
			continue;
		$view .= substr($content, $offset, $start - $offset);
		$view .= "<span class=\"key1\" onclick=\"openKeyword('$blogURL/keylog/" . rawurlencode($keywords[$flag]) . "')\">{$keywords[$flag]}</span>";
		$offset = $start + strlen($keywords[$flag]);
	}
	$view .= substr($content, $offset);
	return $view;
}

function bindAttachments($entryId, $folderPath, $folderURL, $content, $useAbsolutePath = false, $bRssMode = false) {
	global $service, $owner, $hostURL, $blogURL;
	$view = str_replace('[##_ATTACH_PATH_##]', ($useAbsolutePath ? "$hostURL{$service['path']}/attach/$owner" : $folderURL), $content);
	$count = 0;
	$bWritedGalleryJS = false;
	while ((($start = strpos($view, '[##_')) !== false) && (($end = strpos($view, '_##]', $start + 4)) !== false)) {
		$count++;
		$attributes = explode('|', substr($view, $start + 4, $end - $start - 4));
		$prefix = '';
		$postfix = '';
		$buf = '';
		if ($attributes[0] == 'Gallery') {
			if (count($attributes) % 2 == 1)
				array_pop($attributes);
			if (defined('__TATTERTOOLS_MOBILE__') || ($bRssMode == true)) {
				$images = array_slice($attributes, 1, count($attributes) - 2);
				for ($i = 0; $i < count($images); $i++) {
					if (!empty($images[$i])) {
						if ($i % 2 == 0)
							$buf .= '<div align="center">' . getAttachmentBinder($images[$i], '', $folderPath, $folderURL, 1, $useAbsolutePath, $bRssMode) . '</div>';
						else if (strlen($images[$i]) > 0)
							$buf .= "<div align=\"center\">$images[$i]</div>";
					}
				}
			} else {
				$id = "gallery$entryId$count";
				$cssId = "tt-gallery-$entryId-$count";
				$items = array();
				for ($i = 1; $i < sizeof($attributes) - 2; $i += 2)
					array_push($items, array($attributes[$i], $attributes[$i + 1]));
				$galleryAttributes = getAttributesFromString($attributes[sizeof($attributes) - 1]);
				if (($useAbsolutePath == true) && ($bWritedGalleryJS == false)) {
					$bWritedGalleryJS = true;
					$buf .= printScript('gallery.js');
				}
				$buf .= CRLF . '<div id="' . $cssId . '" class="tt-gallery-box">' . CRLF;
				$buf .= '	<script type="text/javascript">' . CRLF;
				$buf .= '		//<![CDATA[' . CRLF;
				$buf .= "			var {$id} = new TTGallery(\"{$cssId}\");" . CRLF;
				$buf .= "			{$id}.prevText = \"" . _text('이전 이미지 보기 버튼') . "\"; " . CRLF;
				$buf .= "			{$id}.nextText = \"" . _text('다음 이미지 보기 버튼') . "\"; " . CRLF;
				$buf .= "			{$id}.enlargeText = \"" . _text('원본 사이즈로 보기 버튼') . "\"; " . CRLF;
				$buf .= "			{$id}.altText = \"" . _text('갤러리 이미지') . "\"; " . CRLF;
				
				foreach ($items as $item) {
					$setWidth = $setHeight = 0;
					if (list($width, $height) = @getimagesize("$folderPath/$item[0]")) {
						$setWidth = $width;
						$setHeight = $height;
						if (isset($galleryAttributes['width']) && $galleryAttributes['width'] < $setWidth) {
							$setHeight = $setHeight * $galleryAttributes['width'] / $setWidth;
							$setWidth = $galleryAttributes['width'];
						}
						if (isset($galleryAttributes['height']) && $galleryAttributes['height'] < $setHeight) {
							$setWidth = $setWidth * $galleryAttributes['height'] / $setHeight;
							$setHeight = $galleryAttributes['height'];
						}
						$buf .= $id . '.appendImage("' . ($useAbsolutePath ? "$hostURL{$service['path']}/attach/$owner/$item[0]" : "$folderURL/$item[0]") . '", "' . htmlspecialchars($item[1]) . '", ' . intval($setWidth) . ', ' . intval($setHeight) . ");";
					}
				}
				$buf .= "			{$id}.show();" . CRLF;
				$buf .= "		//]]>" . CRLF;
				$buf .= '	</script>' . CRLF;
				$buf .= '	<noscript>' . CRLF;
				foreach ($items as $item) {
					if ($useAbsolutePath)
						$buf .= '		<img src="' . $hostURL . $service['path'] . "/attach/" . $owner . "/" . $item[0] . '" alt="' . _text('사용자 삽입 이미지') . '" />' . CRLF;
					else
						$buf .= '		<img src="' . $folderURL . "/" . $item[0] . '" alt="' . _text('사용자 삽입 이미지') . '" />' . CRLF;
					if(!empty($item[1]))
						$buf .= '		<p class="cap1">'.htmlspecialchars($item[1]).'</p>' . CRLF;
				}
				$buf .= '	</noscript>' . CRLF;
				$buf .= '</div>' . CRLF;
			}
		} else if ($attributes[0] == 'iMazing') {
			if (defined('__TATTERTOOLS_MOBILE__')) {
				$images = array_slice($attributes, 1, count($attributes) - 3);
				for ($i = 0; $i < count($images); $i += 2) {
					if (!empty($images[$i]))
						$buf .= '<div>' . getAttachmentBinder($images[$i], '', $folderPath, $folderURL, 1, $useAbsolutePath) . '</div>';
				}
				$buf .= $attributes[count($attributes) - 1];
			} else {
				$params = getAttributesFromString($attributes[sizeof($attributes) - 2]);
				$id = $entryId . $count;
				$imgs = array_slice($attributes, 1, count($attributes) - 3);
				$imgStr = '';
				for ($i = 0; $i < count($imgs); $i += 2) {
					if ($imgs[$i] != '') {
						$imgStr .= $service['path'] . '/attach/' . $owner . '/' . $imgs[$i];
						if ($i < (count($imgs) - 2))
							$imgStr .= '*!';
					}
				}
				if (!empty($attributes[count($attributes) - 1])) {
					$caption = '<p class="cap1">' . $attributes[count($attributes) - 1] . '</p>';
				} else {
					$caption = '';
				}
				$buf .= '<center><img src="' . ($useAbsolutePath ? $hostURL : $service['path']) . '/image/gallery/gallery_enlarge.gif" alt="' . _text('확대') . '" onclick="openFullScreen(\'' . $service['path'] . '/script/gallery/iMazing/embed.php?d=' . urlencode($id) . '&f=' . urlencode($params['frame']) . '&t=' . urlencode($params['transition']) . '&n=' . urlencode($params['navigation']) . '&si=' . urlencode($params['slideshowInterval']) . '&p=' . urlencode($params['page']) . '&a=' . urlencode($params['align']) . '&o=' . $owner . '&i=' . $imgStr . '&r=' . $service['path'] . '\',\'' . str_replace("'", "\\'", $attributes[count($attributes) - 1]) . '\',\'' . $service['path'] . '\')" />';
				$buf .= '<table>';
				$buf .= '<tr>';
				$buf .= '<td width="' . $params['width'] . '" height="' . $params['height'] . '">';
				$buf .= '<div id="iMazingContainer'.$id.'"></div><script type="text/javascript">iMazing' . $id . 'Str = getEmbedCode(\'' . $service['path'] . '/script/gallery/iMazing/main.swf\',\'100%\',\'100%\',\'iMazing' . $id . '\',\'#FFFFFF\',"image=' . $imgStr . '&amp;frame=' . $params['frame'] . '&amp;transition=' . $params['transition'] . '&amp;navigation=' . $params['navigation'] . '&amp;slideshowInterval=' . $params['slideshowInterval'] . '&amp;page=' . $params['page'] . '&amp;align=' . $params['align'] . '&amp;skinPath=' . $service['path'] . '/script/gallery/iMazing/&amp;","false"); writeCode(iMazing' . $id . 'Str, "iMazingContainer'.$id.'");</script><noscript>';
				for ($i = 0; $i < count($imgs); $i += 2)
				    $buf .= '<img src="'.($useAbsolutePath ? $hostURL : $service['path']).'/attach/'.$owner.'/'.$imgs[$i].'" alt="" />';
				$buf .= '</noscript>';
				$buf .= '</td>';
				$buf .= '</tr>';
				$buf .= '</table>' . $caption . '</center>';
			}
		} else if ($attributes[0] == 'Jukebox') {
			if (defined('__TATTERTOOLS_MOBILE__')) {
				$sounds = array_slice($attributes, 1, count($attributes) - 3);
				for ($i = 0; $i < count($sounds); $i += 2) {
					if (!empty($sounds[$i]))
						echo "<a href=\"$folderURL/$sounds[$i]\">$sounds[$i]</a><br />";
				}
			} else {
				$params = getAttributesFromString($attributes[sizeof($attributes) - 2]);
				foreach ($params as $key => $value) {
					if ($key == 'autoPlay') {
						unset($params['autoplay']);
						$params['autoplay'] = $value;
					}
				}
				if ($params['visible'] == 1) {
					$width = '250px';
					$height = '27px';
				} else {
					$width = '0px';
					$height = '0px';
				}
				$id = $entryId . $count;
				$imgs = array_slice($attributes, 1, count($attributes) - 3);
				$imgStr = '';
				for ($i = 0; $i < count($imgs); $i++) {
					if ($imgs[$i] == '')
						continue;
					if ($i % 2 == 1) {
						$imgStr .= urlencode($imgs[$i]) . '_*';
						continue;
					} else {
						if ($i < (count($imgs) - 1))
							$imgStr .= "{$service['path']}/attach/$owner/" . urlencode($imgs[$i]) . '*!';
					}
				}
				if (!empty($attributes[count($attributes) - 1])) {
					$caption = '<div class="cap1" style="text-align: center">' . $attributes[count($attributes) - 1] . '</div>';
				} else {
					$caption = '';
				}
				$buf = '<center>'; 
				$buf .= '<div id="jukeBox' . $id . 'Div" style="width:' . $width . '; height:' . $height . ';"><div id="jukeBoxContainer'.$id.'" style="width:' . $width . '; height:' . $height . ';"></div>';
				$buf .= '<script type="text/javascript">writeCode(getEmbedCode(\'' . $service['path'] . '/script/jukebox/flash/main.swf\',\'100%\',\'100%\',\'jukeBox' . $id . 'Flash\',\'#FFFFFF\',"sounds=' . $imgStr . '&amp;autoplay=' . $params['autoplay'] . '&amp;visible=' . $params['visible'] . '&amp;id=' . $id . '","false"), "jukeBoxContainer'.$id.'")</script><noscript>';
				for ($i = 0; $i < count($imgs); $i++) {
					if ($i % 2 == 0)
						$buf .= '<a href="'.($useAbsolutePath ? $hostURL : $service['path']).'/attach/'.$owner.'/'.$imgs[$i].'">';
					else
						$buf .= htmlspecialchars($imgs[$i]).'</a><br/>';
				}
				$buf .= '</noscript>';
				$buf .= '</div>' . $caption . '</center>';
			}
		} else {
			$contentWidth = getContentWidth();
			
			switch (count($attributes)) {
				case 4:
					list($newProperty, $onclickFlag) = createNewProperty($attributes[1], $contentWidth, $attributes[2]);
					
					if (defined('__TATTERTOOLS_MOBILE__')) {
						$buf = '<div>' . getAttachmentBinder($attributes[1], $newProperty, $folderPath, $folderURL, 1, $useAbsolutePath) . "</div><div>$attributes[3]</div>";
					} else {
						if (trim($attributes[3]) == '') {
							$caption = '';
						} else {
							$caption = '<p class="cap1">' . $attributes[3] . '</p>';
						}
						switch ($attributes[0]) {
							case '1L':
								$prefix = '<div class="imageblock left" style="float: left; margin-right: 10px;">';
								break;
							case '1R':
								$prefix = '<div class="imageblock right" style="float: right; margin-left: 10px;">';
								break;
							case '1C':
							default:
								$prefix = '<div class="imageblock center" style="text-align: center; clear: both;">';
								break;
						}
						$buf = $prefix . getAttachmentBinder($attributes[1], $newProperty, $folderPath, $folderURL, 1, $useAbsolutePath, $bRssMode, $onclickFlag) . $caption . '</div>';
					}
					break;
				case 7:
					$eachImageWidth = floor(($contentWidth - 5 * 3) / 2);
					list($newProperty1, $onclickFlag1) = createNewProperty($attributes[1], $eachImageWidth, $attributes[2]);
					list($newProperty2, $onclickFlag2) = createNewProperty($attributes[4], $eachImageWidth, $attributes[5]);
					if (defined('__TATTERTOOLS_MOBILE__')) {
						$buf = '<div>' . getAttachmentBinder($attributes[1], $newProperty1, $folderPath, $folderURL, 1, $useAbsolutePath, $bRssMode) . "</div><div>$attributes[3]</div>";
						$buf .= '<div>' . getAttachmentBinder($attributes[4], $newProperty2, $folderPath, $folderURL, 1, $useAbsolutePath, $bRssMode) . "</div><div>$attributes[6]</div>";
					} else {
						$buf = '<div class="imageblock dual" style="text-align: center;"><table cellspacing="5" cellpadding="0" border="0" style="margin: 0 auto;"><tr><td>' . getAttachmentBinder($attributes[1], $newProperty1, $folderPath, $folderURL, 2, $useAbsolutePath, $bRssMode, $onclickFlag1) . '<p class="cap1">' . $attributes[3] . '</p></td><td>' . getAttachmentBinder($attributes[4], $newProperty2, $folderPath, $folderURL, 2, $useAbsolutePath, $bRssMode, $onclickFlag2) . '<p class="cap2">' . $attributes[6] . '</p></td></tr></table></div>';
					}
					break;
				case 10:
					$eachImageWidth = floor(($contentWidth - 5 * 4) / 3);
					list($newProperty1, $onclickFlag1) = createNewProperty($attributes[1], $eachImageWidth, $attributes[2]);
					list($newProperty2, $onclickFlag2) = createNewProperty($attributes[4], $eachImageWidth, $attributes[5]);
					list($newProperty3, $onclickFlag3) = createNewProperty($attributes[7], $eachImageWidth, $attributes[8]);
					if (defined('__TATTERTOOLS_MOBILE__')) {
						$buf = '<div>' . getAttachmentBinder($attributes[1], $newProperty1, $folderPath, $folderURL, 1, $useAbsolutePath, $bRssMode) . "</div><div>$attributes[3]</div>";
						$buf .= '<div>' . getAttachmentBinder($attributes[4], $newProperty2, $folderPath, $folderURL, 1, $useAbsolutePath, $bRssMode) . "</div><div>$attributes[6]</div>";
						$buf .= '<div>' . getAttachmentBinder($attributes[7],$newProperty3, $folderPath, $folderURL, 1, $useAbsolutePath, $bRssMode) . "</div><div>$attributes[9]</div>";
					} else {
						$buf = '<div class="imageblock triple" style="text-align: center"><table cellspacing="5" cellpadding="0" border="0" style="margin: 0 auto;"><tr><td>' . getAttachmentBinder($attributes[1], $newProperty1, $folderPath, $folderURL, 3, $useAbsolutePath, $bRssMode, $onclickFlag1) . '<p class="cap1">' . $attributes[3] . '</p></td><td>' . getAttachmentBinder($attributes[4], $newProperty2, $folderPath, $folderURL, 3, $useAbsolutePath, $bRssMode, $onclickFlag2) . '<p class="cap2">' . $attributes[6] . '</p></td><td>' . getAttachmentBinder($attributes[7], $newProperty3, $folderPath, $folderURL, 3, $useAbsolutePath, $bRssMode, $onclickFlag3) . '<p class="cap3">' . $attributes[9] . '</p></td></tr></table></div>';
					}
					break; 
			}
		}
		$view = substr($view, 0, $start) . $buf . substr($view, $end + 4);
	}
	return $view;
}

function getAttachmentBinder($filename, $property, $folderPath, $folderURL, $imageBlocks = 1, $useAbsolutePath = false, $bRssMode = false, $onclickFlag=false) {
	global $database, $skinSetting, $service, $owner, $blogURL, $hostURL, $waterMarkArray, $paddingArray;
	$path = "$folderPath/$filename";
	if ($useAbsolutePath)
		$url = "$hostURL{$service['path']}/attach/$owner/$filename";
	else
		$url = "$folderURL/$filename";
	$fileInfo = getAttachmentByOnlyName($owner, $filename);
	switch (getFileExtension($filename)) {
		case 'jpg':case 'jpeg':case 'gif':case 'png':case 'bmp':
			if (defined('__TATTERTOOLS_MOBILE__')) {
				return fireEvent('ViewAttachedImageMobile', "<img src=\"$blogURL/imageResizer/?f=" . urlencode($filename) . "\" alt=\"\" />", $path);
			} else { /*if ($bRssMode == true) {
				$property = str_replace('&quot;', '"', $property);
				return fireEvent('ViewAttachedImage', "<img src=\"$url\" $property/>", $path);
			} else {*/
				if ($onclickFlag == true) {
					$imageStr = '<img src="'.$url.'" '.$property.' style="cursor: pointer;" onclick="open_img(\''.$url.'\')" />';
				} else {
					$imageStr = '<img src="'.$url.'" '.$property.' />';		
				}
				
				if (!is_dir(ROOT."/cache/thumbnail/$owner")) { 
					@mkdir(ROOT."/cache/thumbnail");
					@chmod(ROOT."/cache/thumbnail", 0777);
					@mkdir(ROOT."/cache/thumbnail/$owner");
					@chmod(ROOT."/cache/thumbnail/$owner", 0777);
				}
				
				return makeThumbnail(fireEvent('ViewAttachedImage', $imageStr, $path), $path, $paddingArray, $waterMarkArray);
			}
			break;
		case 'swf':
			$id = md5($url) . rand(1, 10000);
			return "<span id=\"$id\"><script type=\"text/javascript\">writeCode(getEmbedCode('$url','300','400','$id','#FFFFFF',''), \"$id\");</script></span>";
			break;
		case 'wmv':case 'avi':case 'asf':case 'mpg':case 'mpeg':
			$id = md5($url) . rand(1, 10000);
			return "<span id=\"$id\"><script type=\"text/javascript\">writeCode('<embed $property autostart=\"0\" src=\"$url\"></embed>', \"$id\")</script></span>";
			break;
		case 'mp3':case 'mp2':case 'wma':case 'wav':case 'mid':case 'midi':
			$id = md5($url) . rand(1, 10000);
			return "<span id=\"$id\"><script type=\"text/javascript\">writeCode('<embed $property autostart=\"0\" height=\"45\" src=\"$url\"></embed>', \"$id\")</script></span>";
			break;
		case 'mov':
			$id = md5($url) . rand(1, 10000);
			return "<span id=\"$id\"><script type=\"text/javascript\">writeCode(" . '\'<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="400" height="300"><param name="src" value="' . $url . '" /><param name="controller" value="true" /><param name="pluginspage" value="http://www.apple.com/QuickTime/download/" /><!--[if !IE]> <--><object type="video/quicktime" data="' . $url . '" width="400" height="300" class="mov"><param name="controller" value="true" /><param name="pluginspage" value="http://www.apple.com/QuickTime/download/" /></object><!--> <![endif]--></object>\'' . ", \"$id\")</script></span>";
			break;
		default:
			if (file_exists(ROOT . '/image/extension/' . getFileExtension($filename) . '.gif')) {
				return '<a href="' . ($useAbsolutePath ? $hostURL : '') . $blogURL . '/attachment/' . $filename . '"><img src="' . ($useAbsolutePath ? $hostURL : '') . $service['path'] . '/image/extension/' . getFileExtension($filename) . '.gif" alt="' . _text('첨부 파일 아이콘') . '" /> ' . htmlspecialchars($fileInfo['label']) . '</a>';
			} else {
				return '<a href="' . ($useAbsolutePath ? $hostURL : '') . $blogURL . '/attachment/' . $filename . '"><img src="' . ($useAbsolutePath ? $hostURL : '') . $service['path'] . '/image/extension/unknown.gif" alt="' . _text('첨부 파일 아이콘') . '" /> ' . htmlspecialchars($fileInfo['label']) . '</a>';
			}
			break;
	}
}

function printFeedGroups($owner, $selectedGroup = 0, $starredOnly = false, $searchKeyword = null) {
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
	foreach (getFeedGroups($owner, $starredOnly, $searchKeyword) as $group) {
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
																<a class="delete-button button" href="#void" onclick="Reader.deleteGroup()"><span class="text"><?php echo _t('삭제하기');?></span></a>
																<span class="divider">|</span>
																<a class="edit-button button" href="#void" onclick="Reader.editGroupExecute()"><span class="text"><?php echo _t('저장하기');?></span></a>
																<span class="divider">|</span>
																<a class="cancel-button button" href="#void" onclick="Reader.cancelEditGroup()"><span class="text"><?php echo _t('취소하기');?></span></a>
															</div>
														</div>
													</div>
<?php 
}

function printFeeds($owner, $group = 0, $starredOnly = false, $searchKeyword = null) {
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
	foreach (getFeeds($owner, $group, $starredOnly, $searchKeyword) as $feed) {
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
																<a id="iconFeedStatus<?php echo $feed['id'];?>" class="update-button button" onclick="Reader.updateFeed(<?php echo $feed['id'];?>, '<?php echo _t('피드를 업데이트 했습니다.');?>'); event.cancelBubble=true; return false;" title="<?php echo _t('이 피드를 업데이트 합니다.');?>"><span class="text"><?php echo _t('피드 업데이트');?></span></a>
																<span class="divider">|</span>
																<a class="edit-button button" href="#void" onclick="Reader.editFeed(<?php echo $feed['id'];?>, '<?php echo escapeJSInAttribute($feed['xmlURL']);?>')" title="<?php echo _t('이 피드 정보를 수정합니다.');?>"><span class="text"><?php echo _t('수정');?></span></a>
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
	foreach (getFeedGroups($owner) as $group) {
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
																<a class="delete-button button" href="#void" onclick="Reader.deleteFeed()"><span class="text"><?php echo _t('삭제하기');?></span></a>
																<span class="divider">|</span>
																<a class="edit-button button" href="#void" onclick="Reader.editFeedExecute()"><span class="text"><?php echo _t('저장하기');?></span></a>
																<span class="divider">|</span>
																<a class="cancel-button button" href="#void" onclick="Reader.cancelEditFeed()"><span class="text"><?php echo _t('취소하기');?></span></a>
															</div>
														</div>
													</div>
<?php 
}



function printFeedEntries($owner, $group = 0, $feed = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null) {
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
	foreach (getFeedEntries($owner, $group, $feed, $unreadOnly, $starredOnly, $searchKeyword) as $entry) {
		if ($count == 0)
			$firstEntryId = $entry['id'];
		$className = $entry['wasread'] ? 'read' : 'unread';
		$className .= ($count % 2) == 1 ? ' even-line' : ' odd-line';
		$className .= ($count == 0) ? ' active-class' : ' inactive-class';
		$podcast = $entry['enclosure'] ? '<span class="podcast-icon bullet" title="'._t('팟캐스트 포스트입니다.').'"><span class="text">' . _t('팟캐스트') . '</span></span>' : '';
?>
														<tr id="entryTitleList<?php echo $entry['id'];?>" class="<?php echo $className;?>" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="Reader.selectEntry(<?php echo $entry['id'];?>)">
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
														Reader.setTotalEntries(<?php echo getFeedEntriesTotalCount($owner, $group, $feed, $unreadOnly, $starredOnly, $searchKeyword);?>);
<?php 
	if (isset($firstEntryId)) {
?>
														Reader.selectedEntryObject = document.getElementById("entryTitleList<?php echo $firstEntryId;?>").parentNode;
<?php 
	}
?>
													//]]>
												</script>
<?php 
	return $count;
}

function printFeedEntriesMore($owner, $group = 0, $feed = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null, $offset) {
	global $service;
?>
												<table cellpadding="0" cellspacing="0">
<?php
	$count = 1;
	foreach (getFeedEntries($owner, $group, $feed, $unreadOnly, $starredOnly, $searchKeyword, $offset) as $entry) {
		$class = $entry['wasread'] ? 'read' : 'unread';
		$class .= ($count % 2) == 1 ? ' odd-line' : ' even-line';
		$class .= ' inactive-class';
		$podcast = $entry['enclosure'] ? '<span class="podcast-icon bullet" title="'._t('팟캐스트 포스트입니다.').'"><span class="text">' . _t('팟캐스트') . '</span></span>' : '';
?>
													<tr id="entryTitleList<?php echo $entry['id'];?>" class="<?php echo $class;?>" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="Reader.selectEntry(<?php echo $entry['id'];?>)">
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

function printFeedEntry($owner, $group = 0, $feed = 0, $entry = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null, $position = 'current') {
	global $service;
	if (!$entry = getFeedEntry($owner, $group, $feed, $entry, $unreadOnly, $starredOnly, $searchKeyword, $position)) {
		$entry = array('id' => 0, 'author' => 'Tattertools', 'blog_title' => 'Tattertools Reader', 'permalink' => '#', 'entry_title' => _t('포스트가 없습니다.'), 'language' => 'en-US', 'description' => '<div style="height: 369px"></div>', 'tags' => '', 'enclosure' => '', 'written' => time());
	}
?>
												<div id="entryHead">
													<div class="title"><a href="<?php echo htmlspecialchars($entry['permalink']);?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($entry['entry_title']);?></a></div>
													<div class="writing-info"><span class="by">by </span><span class="name"><?php echo htmlspecialchars($entry['author'] ? eregi_replace("^\((.+)\)$", "\\1", $entry['author']) : $entry['blog_title']);?></span><span class="divider"> : </span><span class="date"><?php echo date('Y-m-d H:i:s', $entry['written']);?></span></div>
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
														<a class="non-read-button button" href="#void" onclick="Reader.markAsUnread(<?php echo $entry['id'];?>)"><span class="text"><?php echo _t('안 읽은 글로 표시');?></span></a>
													</div>
												</div>
<?php 
}

function printScript($filename, $obfuscate = true) {
	global $service, $hostURL, $blogURL;
	if (!$file = @file_get_contents(ROOT . "/script/$filename"))
		return '';
	$file = "<script type=\"text/javascript\">var servicePath=\"$hostURL{$service['path']}\"; var blogURL=\"$hostURL$blogURL/\";$file";
	if ($obfuscate) {
	}
	return "$file</script>";
}

function createNewProperty($filename, $imageWidth, $property) {
	global $owner;
	
	if ($tempInfo = getimagesize(ROOT."/attach/$owner/$filename")) {
		list($originWidth, $originHeight, $type, $attr) = $tempInfo;
	} else {
		return array($property, false);
	}
	
	if (eregi('alt=""', $property)) {
		$property = str_replace('alt=""', 'alt="'._text('사용자 삽입 이미지').'"', $property);
	} else if (eregi('alt="[^"]+"', $property)) {
		// 이미 있으므로 통과
	} else {
		$property .= ' alt="'._text('사용자 삽입 이미지').'"';	
	}
	
	// 현재 이미지의 가로 사이즈 계산.
	if (eregi('width="([0-9]*%?)"', $property, $temp)) {
		$currentWidth = $temp[1];
		if (eregi("^([0-9]+)%$", $currentWidth)) {
			$currentWidth = $originWidth * ($temp[1]/100);
		}
	} else {
		$property .= ' width="1"';
	}
	
	// 현재 이미지의 세로 사이즈 계산.
	if (eregi('height="([0-9]*%?)"', $property, $temp)) {
		$currentHeight = $temp[1];
		if (eregi("^([0-9]+)%$", $currentHeight)) {
			$currentHeight = $originHeight * ($temp[1]/100);
		}
	} else {
		$property .= ' height="1"';
	}
	
	// 가로만 지정된 이미지의 경우.
	if (isset($currentWidth) && !isset($currentHeight)) {
		// 비어있는 세로를 가로의 크기를 이용하여 계산.
		$currentHeight = floor($originHeight * $currentWidth / $originWidth);
	// 세로만 지정된 이미지의 경우.
	} else if (!isset($currentWidth) && isset($currentHeight)) {
		// 비어있는 가로를 세로의 크기를 이용하여 계산.
		$currentWidth = floor($originWidth * $currentHeight / $originHeight);
	// 둘 다 지정되지 않은 이미지의 경우.
	} else if (!isset($currentWidth) && !isset($currentHeight)) {
		// 둘 다 비어 있을 경우는 오리지널 사이즈로 대치.
		$currentWidth = $originWidth;
		$currentHeight = $originHeight;
	}
	
	if ($currentWidth > $imageWidth) {
		$tempWidth = $imageWidth;
		$tempHeight = floor($currentHeight * $imageWidth / $currentWidth);
	} else {
		$tempWidth = $currentWidth;
		$tempHeight = $currentHeight;
	}
	
	$property = eregi_replace('(^| )width="([0-9]+%?)"', '\1width="'.$tempWidth.'"', $property);
	$property = eregi_replace('(^| )height="([0-9]+%?)"', '\1height="'.$tempHeight.'"', $property);
	
	if ($originWidth > $tempWidth || $originHeight > $tempHeight) {
		$onclickFlag = true;
	} else {
		$onclickFlag = false;
	}
	
	return array($property, $onclickFlag);
}
?>
