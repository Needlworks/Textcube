<?

function printHtmlHeader($title = '') {
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title><?=$title?></title>
</head>
<body>
<?
}

function printHtmlFooter() {
?>
</body>
</html>
<?
}

function dress($tag, $value, & $contents) {
	$contents = str_replace("[##_{$tag}_##]", $value, $contents);
}

function getUpperView($paging) {
	global $g_version, $service, $blogURL;
	ob_start();
?>
<!--
	<?=$g_version?>
	
	Homepage: http://www.tattertools.com
	Copyright (c) 2005 Tatter & Company, LLP. All rights reserved.
-->
<script type="text/javascript">
var servicePath = "<?=$service['path']?>"; var blogURL = "<?=$blogURL?>";
</script>
<script type="text/javascript" src="<?=$service['path']?>/script/EAF.js"></script>
<script type="text/javascript" src="<?=$service['path']?>/script/common.js"></script>
<script type="text/javascript" src="<?=$service['path']?>/script/gallery.js" ></script>
<?
	if (doesHaveOwnership()) {
?>
<script type="text/javascript" src="<?=$service['path']?>/script/owner.js" ></script>
<?
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
				window.location = "<?=$blogURL?>/owner";
				break;
			case 82: //R
				window.location = "<?=$blogURL?>/owner/reader";
				break;
			case 84: //T
				window.location = "<?=$blogURL?>/owner/reader/?forceRefresh";
				break;
<?
	if (isset($paging['prev'])) {
?>
			case 65: //A
				window.location = "<?=escapeJSInCData("{$paging['url']}{$paging['prefix']}{$paging['prev']}{$paging['postfix']}")?>";
				break;
<?
	}
	if (isset($paging['next'])) {
?>
			case 83: //S
				window.location = "<?=escapeJSInCData("{$paging['url']}{$paging['prefix']}{$paging['next']}{$paging['postfix']}")?>";
				break;
<?
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
				document.getElementById("commentCount" + entryId).innerHTML = this.getText("/response/commentCount");
			if(document.getElementById("commentCountOnRecentEntries" + entryId))
				document.getElementById("commentCountOnRecentEntries" + entryId).innerHTML = this.getText("/response/commentCount");
		}
		request.onError = function() {
			alert(this.getText("/response/description"));
		}
		var queryString = "key=<?=md5(filemtime(ROOT . '/config.php'))?>";
		if(oForm["name"])
			queryString += "&name_" + entryId +"=" + encodeURIComponent(oForm["name"].value);
		if(oForm["password"])
			queryString += "&password_" + entryId +"=" + encodeURIComponent(oForm["password"].value);
		if(oForm["homepage"])
			queryString += "&homepage_" + entryId +"=" + encodeURIComponent(oForm["homepage"].value);
		if(oForm["secret"] && oForm["secret"].checked)
			queryString += "&secret_" + entryId +"=1";
		if(oForm["comment"])
			queryString += "&comment_" + entryId +"=" + encodeURIComponent(oForm["comment"].value);
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
		openWindow = window.open("<?=$blogURL?>/comment/delete/" + id, "tatter", "width="+width+",height="+height+",location=0,menubar=0,resizable=0,scrollbars=0,status=0,toolbar=0");
		openWindow.focus();
		alignCenter(openWindow,width,height);
	}
	
	
	
	function commentComment(parent) {	
		width = 450;
		height = 360;
		if(openWindow != '') openWindow.close();
		openWindow = window.open("<?=$blogURL?>/comment/comment/" + parent, "tatter", "width="+width+",height="+height+",location=0,menubar=0,resizable=0,scrollbars=0,status=0,toolbar=0");
		openWindow.focus();
		alignCenter(openWindow,width,height);
	}
	
	function editEntry(parent,child) {	
		width =  825;
		height = 550;
		if(openWindow != '') openWindow.close();
		openWindow = window.open("<?=$blogURL?>/owner/entry/edit/" + parent + "?popupEditor&returnURL=" + child,"tatter", "width="+width+",height="+height+",location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0");
		openWindow.focus();
		alignCenter(openWindow,width,height);
	}
	
	function guestbookComment(parent) {	
		width = 450;
		height = 360;
		if(openWindow != '') openWindow.close();
		openWindow = window.open("<?=$blogURL?>/comment/comment/" + parent, "tatter", "width="+width+",height="+height+",location=0,menubar=0,resizable=0,scrollbars=0,status=0,toolbar=0");
		openWindow.focus();
		alignCenter(openWindow,width,height);
	}
	
	function sendTrackback(id) {
		width = 700;
		height = 500;
		if(openWindow != '') openWindow.close();
		openWindow = window.open("<?=$blogURL?>/trackback/send/" + id, "tatter", "width=580,height=400,location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0");
		openWindow.focus();
		alignCenter(openWindow,width,height);
	}

	function copyUrl(url){		
		if(isIE) {
			window.clipboardData.setData('Text',url);
			window.alert("<?=_t('엮인글 주소가 복사되었습니다')?>");
		}
	}
	
	
	function deleteTrackback(id,entryId) {
<?
	if (doesHaveOwnership()) {
?> 
		if (!confirm("<?=_t('선택된 트랙백을 삭제합니다. 계속하시겠습니까?\t')?>"))
			return;

		var request = new HTTPRequest("GET", "<?=$blogURL?>/trackback/delete/" + id);
		request.onSuccess = function() {
			document.getElementById('entry'+entryId+'Trackback').innerHTML= this.getText("/response/result");
		}
		request.onError = function() {
			alert('<?=_t('실패 했습니다')?>');
		}
		request.send();
<?
	} else {
?>
		alert('<?=_t('실패 했습니다')?>');
<?
	}
?>
	}
<?
	if (doesHaveOwnership()) {
?>
	function changeVisibility(id, visibility) {
		var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/visibility/" + id + "?visibility=" + visibility);
		request.onSuccess = function() {
			window.location.reload();
		}
		request.send();
	}
	
	function deleteEntry(id) {
		if (!confirm("<?=_t('이 글 및 이미지 파일을 완전히 삭제합니다. 계속하시겠습니까?\t')?>"))
			return;
		var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/delete/" + id);
		request.onSuccess = function() {
			window.location.reload();
		}
		request.send();
	}	
<?
	}
?>

	function reloadEntry(id) {
		var password = document.getElementById("entry" + id + "password");
		if (!password)
			return;
		document.cookie = "GUEST_PASSWORD=" + escape(password.value) + ";path=<?=$blogURL?>";
		
		var request = new HTTPRequest("POST", "<?=$blogURL?>/" + id);
		request.async = false;
		request.send("partial=");
		var entry = document.getElementById("entry" + id);
		if (entry)
			entry.innerHTML = request.getText();
	}
//]]>
</script>
<?
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
	<?
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getTrackbacksView($entryId, & $skin) {
	global $suri, $hostURL, $blogURL, $skinSetting;
	$trackbacksView = '';
	foreach (getTrackbacks($entryId) as $trackback) {
		$trackbackView = "<a id=\"trackback{$trackback['id']}\"></a>" . $skin->trackback;
		dress('tb_rep_title', htmlspecialchars($trackback['subject']), $trackbackView);
		dress('tb_rep_site', htmlspecialchars($trackback['site']), $trackbackView);
		dress('tb_rep_url', htmlspecialchars($trackback['url']), $trackbackView);
		dress('tb_rep_desc', htmlspecialchars($trackback['excerpt']), $trackbackView);
		dress('tb_rep_onclick_delete', "deleteTrackback({$trackback['id']}, $entryId)", $trackbackView);
		dress('tb_rep_date', Timestamp::format5($trackback['written']), $trackbackView);
		$trackbacksView .= $trackbackView;
	}
	if ($skinSetting['expandTrackback'] == 1 || (($suri['directive'] == '/' || $suri['directive'] == '/entry') && $suri['value'] != '')) {
		$style = 'block';
	} else {
		$style = 'none';
	}
	$trackbacksView = "<div id=\"entry{$entryId}Trackback\" style=\"display:$style\">" . str_replace('[##_tb_rep_##]', $trackbacksView, $skin->trackbacks) . '</div>';
	dress('tb_address', "<span onclick=\"copyUrl('$hostURL$blogURL/trackback/$entryId')\">$hostURL$blogURL/trackback/$entryId</span>", $trackbacksView);
	return $trackbacksView;
}

function getCommentView($entryId, & $skin) {
	global $blogURL, $owner, $suri, $paging, $blog;
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
		$prefix2 = 'guest';
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
			dress($prefix1 . '_rep_desc', fireEvent(($isComment ? 'ViewCommentContent' : 'ViewGuestCommentContent'), nl2br(addLinkSense(htmlspecialchars($commentSubItem['comment']), ' onclick="return openLinkInNewWindow(this)"')), $commentSubItem), $commentSubItemView);
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
		dress($prefix1 . '_rep_desc', fireEvent(($isComment ? 'ViewCommentContent' : 'ViewGuestCommentContent'), nl2br(addLinkSense(htmlspecialchars($commentItem['comment']), ' onclick="return openLinkInNewWindow(this)"')), $commentItem), $commentItemView);
		dress($prefix1 . '_rep_date', Timestamp::format5($commentItem['written']), $commentItemView);
		if ($prefix1 == 'guest' && $authorized != true && $blogSetting['allowWriteDoubleCommentOnGuestbook'] == 0) {
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

function getCategoriesView($categories, $selected, $skin, $xhtml = false) {
	global $blogURL, $owner;
	if (doesHaveOwnership()) {
		$entriesSign = 'entriesInLogin';
	} else {
		$entriesSign = 'entries';
	}
	$tree = array('id' => 0, 'label' => _t('전체'), 'value' => getEntriesTotalCount($owner), 'link' => "$blogURL/category", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		foreach ($category1['children'] as $category2) {
			array_push($children, array('id' => $category2['id'], 'label' => $category2['name'], 'value' => $category2[$entriesSign], 'link' => "$blogURL/category/" . encodeURL($category1['name'] . '/' . $category2['name']), 'children' => array()));
		}
		array_push($tree['children'], array('id' => $category1['id'], 'label' => $category1['name'], 'value' => $category1[$entriesSign], 'link' => "$blogURL/category/" . encodeURL($category1['name']), 'children' => $children));
	}
	ob_start();
	printTreeView($tree, $selected, $skin, $xhtml);
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getCategoriesViewInOwner($categories, $selected, $skin) {
	global $blogURL, $owner;
	if (doesHaveOwnership()) {
		$entriesSign = 'entriesInLogin';
	} else {
		$entriesSign = 'entries';
	}
	$tree = array('id' => 0, 'label' => _t('전체'), 'value' => getEntriesTotalCount($owner), 'link' => "$blogURL/owner/entry/category", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		foreach ($category1['children'] as $category2) {
			array_push($children, array('id' => $category2['id'], 'label' => $category2['name'], 'value' => $category2[$entriesSign], 'link' => "$blogURL/owner/entry/category/?id={$category2['id']}&entries={$category2['entries']}&priority={$category1['priority']}&name1=" . rawurlencode($category1['name']) . "&name2=" . rawurlencode($category2['name']), 'children' => array()));
		}
		array_push($tree['children'], array('id' => $category1['id'], 'label' => $category1['name'], 'value' => $category1[$entriesSign], 'link' => "$blogURL/owner/entry/category/?&id={$category1['id']}&entries={$category1['entries']}&priority={$category1['priority']}&name1=" . rawurlencode($category1['name']), 'children' => $children));
	}
	ob_start();
	printTreeView($tree, $selected, $skin);
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function getCategoriesViewInSkinSetting($categories, $selected, $skin) {
	global $owner;
	if (doesHaveOwnership()) {
		$entriesSign = 'entriesInLogin';
	} else {
		$entriesSign = 'entries';
	}
	$tree = array('id' => 0, 'label' => _t('전체'), 'value' => getEntriesTotalCount($owner), 'link' => "", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		foreach ($category1['children'] as $category2) {
			array_push($children, array('id' => $category2['id'], 'label' => $category2['name'], 'value' => $category2[$entriesSign], 'link' => "", 'children' => array()));
		}
		array_push($tree['children'], array('id' => $category1['id'], 'label' => $category1['name'], 'value' => $category1[$entriesSign], 'link' => "", 'children' => $children));
	}
	ob_start();
	printTreeView($tree, $selected, $skin);
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

function printTreeView($tree, $selected, $skin, $xhtml = false) {
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
<?
	foreach ($tree['children'] as $level1) {
		if (!empty($level1['children'])) {
?>
		expandFolder(<?=$level1['id']?>, true);
<?
		}
	}
?>
	}
	
	function expandFolder(category, expand) {
		var oLevel1 = document.getElementById("category_" + category);
		var oImg = oLevel1.getElementsByTagName("img")[0];
		switch (expand) {
			case true:
				oImg.src = "<?=$skin['url']?>/tab_opened.gif";
				showLayer("category_" + category + "_children");
				return true;
			case false:
				oImg.src = "<?=$skin['url']?>/tab_closed.gif";
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
				oImg.src = "<?=$skin['url']?>/tab_opened.gif";
				showLayer("category_" + category + "_children");
				expanded = true;
				return true;
			case "opened":
				oImg.src = "<?=$skin['url']?>/tab_closed.gif";
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
			
			oChild.style.color = "#<?=$skin['itemColor']?>";			
<?
	if ($skin['itemBgColor'] != '')
		echo "			oChild.style.backgroundColor = \"#{$skin['itemBgColor']}\"";
	else
		echo "			oChild.style.backgroundColor = \"\"";
?>			
						
			root.setAttribute('currentselectednode',category);
			document.getElementById('text_'+selectedNode).style.color="#<?=$skin['itemColor']?>";
			
			var oLevel = document.getElementById("category_" + category);
			var oChild = oLevel.getElementsByTagName("table")[0];
			oChild.style.color = "#<?=$skin['activeItemColor']?>";
<?
	if ($skin['activeItemBgColor'] != '')
		echo "			oChild.style.backgroundColor = \"#{$skin['activeItemBgColor']}\"";
	else
		echo "			oChild.style.backgroundColor = \"\"";
?>			
			
			document.getElementById('text_'+category).style.color="#<?=$skin['activeItemColor']?>";
			
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
	<?
	if ($skin['itemBgColor'] == "") {
		$itemBgColor = '';
	} else {
		$itemBgColor = 'background-color: #' . $skin['itemBgColor'] . ';';
	}
?>
	<table id="treeComponent" currentselectednode="<?=$selected?>" cellpadding="0" cellspacing="0" style="width: 100%;"><tr>
	<td>
		<table id="category_0" name="treeNode" cellpadding="0" cellspacing="0"><tr>
			<td class="ib" style="font-size: 1px"><img src="<?=$skin['url']?>/tab_top.gif" width="16" onclick="expandTree()" alt=""/></td>
			<td valign="top" style="font-size:9pt; padding-left:3px">
				<table onclick="<?
	if ($action == 1) {
?> alert(3);onclick_setimp(window, this, c_ary, t_ary); <?
	}
?>" id="imp0" cellpadding="0" cellspacing="0" style="<?=$itemBgColor?>"><tr>
					<?
	if (empty($tree['link']))
		$link = 'onclick="selectNode(0)"';
	else
		$link = 'onclick="window.location.href=\'' . escapeJSInAttribute($tree['link']) . '\'"';
?>
					<td class="branch3" <?=$link?>><div id="text_0" style=" color: #<?=$skin['itemColor']?>;"><?=htmlspecialchars($tree['label'])?> <?
	if ($skin['showValue'])
		print "<span class=\"c_cnt\">({$tree['value']})</span>";
?></div></td>
				</tr></table>
			</td>
		</tr></table>

<?
	$parentOfSelected = false;
	$i = count($tree['children']);
	foreach ($tree['children'] as $row) {
		$i--;
		if (empty($row['link']))
			$link = 'onclick="selectNode(' . $row['id'] . ')"';
		else
			$link = 'onclick="window.location.href=\'' . escapeJSInAttribute($row['link']) . '\'"';
?>
		<table name="treeNode"  id="category_<?=$row['id']?>" cellpadding="0" cellspacing="0"><tr>
			<td class="ib" style="width:39px; font-size: 1px; background-image: url('<?=$skin['url']?>/navi_back_noactive<?=($i ? '' : '_end')?>.gif')"><a class="click" onclick="toggleFolder('<?=$row['id']?>')"><img src="<?=$skin['url']?>/tab_<?=(count($row['children']) ? 'closed' : 'isleaf')?>.gif" width="39" alt=""/></a></td>
			<td>
				<table cellpadding="0" cellspacing="0" style="<?=$itemBgColor?>"><tr>
					<td class="branch3" <?=$link?>><div id="text_<?=$row['id']?>" style="color: #<?=$skin['itemColor']?>;"><?=htmlspecialchars(UTF8::lessenAsEm($row['label'], $skin['labelLength']))?> <?
		if ($skin['showValue'])
			print "<span class=\"c_cnt\">({$row['value']})</span>";
?></div></td>
				</tr></table>
			</td>
		</tr></table>
		<div id="category_<?=$row['id']?>_children" style="display:none">
<?
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
				<table id="category_<?=$irow['id']?>" name="treeNode" cellpadding="0" cellspacing="0"><tr>
				<td style="width:39px; font-size: 1px"><img src="<?=$skin['url']?>/navi_back_active<?=($i ? '' : '_end')?>.gif" width="17" height="18" alt=""/><img src="<?=$skin['url']?>/tab_treed<?
			if (!$j)
				print "_end";
?>.gif" width="22" alt=""/></td>
				<td>
					<table <?=$link?> cellpadding="0" cellspacing="0" style="<?=$itemBgColor?>"><tr>
					<td class="branch3"><div id="text_<?=$irow['id']?>" style="color: #<?=$skin['itemColor']?>;"><?=htmlspecialchars(UTF8::lessenAsEm($irow['label'], $skin['labelLength']))?> <?=($skin['showValue'] ? "<span class=\"c_cnt\">({$irow['value']})</span>" : '')?></div></td>
					</tr></table>
				</td>
				</tr></table>
<?
		}
?>
		</div>
<?
	}
?>
	</td></tr></table>
<?
	if (is_numeric($selected)) {
?>
<script type="text/javascript">
//<![CDATA[
<?
		if ($parentOfSelected) {
?>
	expandFolder(<?=$parentOfSelected?>, true);
<?
		}
?>
	selectNode(<?=$selected?>);
//]]>
</script>
<?
	}
}

function getArchivesView($archives, & $template) {
	global $blogURL;
	ob_start();
	foreach ($archives as $archive) {
		$view = "$template";
		dress('archive_rep_link', "$blogURL/archive/{$archive['period']}", $view);
		dress('archive_rep_date', getPeriodLabel($archive['period']), $view);
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
	ob_start();
?>
<table cellpadding="0" cellspacing="1" style="width: 100%; table-layout: fixed">
<caption class="cal_month">
<a href="<?=$blogURL?>/archive/<?=$previous?>">&lt;&lt;</a>
&nbsp;
<a href="<?=$blogURL?>/archive/<?=$current?>"><?=Timestamp::format('%Y/%m', getTimeFromPeriod($current))?></a>
&nbsp;
<a href="<?=$blogURL?>/archive/<?=$next?>">&gt;&gt;</a>
</caption>
<thead>
  <tr>
    <th class="cal_week2">S</th>
    <th class="cal_week1">M</th>
    <th class="cal_week1">T</th>
    <th class="cal_week1">W</th>
    <th class="cal_week1">T</th>
    <th class="cal_week1">F</th>
    <th class="cal_week1">S</th>
  </tr>
</thead>
<tbody>
  <tr>
<?
	for ($weekday = 0; $weekday < $firstWeekday; $weekday++)
		echo '    <td class="cal_day1"></td>', CRLF;
	for ($day = 1; $weekday < 7; $weekday++, $day++) {
		if (isset($calendar['days'][$day]))
			echo '    <td class="', ($day == $today ? 'cal_day4' : 'cal_day3'), "\"><a class=\"cal_click\" href=\"$blogURL/archive/$current", ($day > 9 ? $day : '0' . $day), "\">$day</a></td>", CRLF;
		else
			echo '    <td class="', ($day == $today ? 'cal_day4' : 'cal_day3'), "\">$day</td>", CRLF;
	}
	echo '  </tr>', CRLF;
	while (true) {
		echo '  <tr>', CRLF;
		for ($weekday = 0; ($weekday < 7) && ($day <= $lastDay); $weekday++, $day++)
			if (isset($calendar['days'][$day]))
				echo '    <td class="', ($day == $today ? 'cal_day4' : 'cal_day3'), "\"><a class=\"cal_click\" href=\"$blogURL/archive/$current", ($day > 9 ? $day : '0' . $day), "\">$day</a></td>", CRLF;
			else
				echo '    <td class="', ($day == $today ? 'cal_day4' : 'cal_day3'), "\">$day</td>", CRLF;
		if ($day > $lastDay) {
			for (; $weekday < 7; $weekday++)
				echo '    <td class="cal_day2"></td>', CRLF;
			echo '  </tr>', CRLF;
			break;
		}
		echo '  </tr>', CRLF;
	}
?>
</tbody>
</table>
<?
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
		dress('rctps_rep_rp_cnt', "<span id=\"commentCountOnRecentEntries{$entry['id']}\">".($entry['comments'] > 0 ? "({$entry['comments']})" : '').'</span>', $view);
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
		dress('rctrp_rep_time', Timestamp::format2($comment['written']), $view);
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
		dress('rcttb_rep_time', Timestamp::format2($trackback['written']), $view);
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

function getEntryContentView($owner, $id, $content, $keywords = array(), $type = 'Post', $useAbsolutePath = false) {
	global $service;
	$path = ROOT . "/attach/$owner";
	$url = "{$service['path']}/attach/$owner";
	$view = bindAttachments($id, $path, $url, $content, $useAbsolutePath);
	$view = bindKeywords($keywords, $view);
	$view = bindTags($id, $view);
	if (defined('__TATTERTOOLS_MOBILE__'))
		$view = stripHTML($view, array('a', 'abbr', 'acronym', 'address', 'b', 'blockquote', 'br', 'cite', 'code', 'dd', 'del', 'dfn', 'div', 'dl', 'dt', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins', 'kbd', 'li', 'ol', 'p', 'pre', 'q', 's', 'samp', 'span', 'strike', 'strong', 'sub', 'sup', 'u', 'ul', 'var'));
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
			$content .= "<div>[$more | $less]<br/>$full</div>";
		} else {
			$content .= "<div id=\"more{$id}_$no\" style=\"display:block\"><a href=\"#\" onclick=\"hideLayer('more{$id}_$no');showLayer('less{$id}_$no');return false\">$more</a></div>";
			$content .= "<div id=\"less{$id}_$no\" style=\"display:none\"><a href=\"#\" onclick=\"showLayer('more{$id}_$no');hideLayer('less{$id}_$no');return false\">$less</a>$full</div>";
		}
		$content .= $postfix;
	}
	return $content;
}

function bindKeywords($keywords, $content) {
	return $content;
}

function bindAttachments($entryId, $folderPath, $folderURL, $content, $useAbsolutePath = false) {
	global $service, $owner, $hostURL, $blogURL;
	$view = str_replace('[##_ATTACH_PATH_##]', ($useAbsolutePath ? "$hostURL{$service['path']}/attach/$owner" : $folderURL), $content);
	$count = 0;
	while ((($start = strpos($view, '[##_')) !== false) && (($end = strpos($view, '_##]', $start + 4)) !== false)) {
		$count++;
		$attributes = explode('|', substr($view, $start + 4, $end - $start - 4));
		$prefix = '';
		$postfix = '';
		$buf = '';
		if ($attributes[0] == 'Gallery') {
			if (count($attributes) % 2 == 1)
				array_pop($attributes);
			if (defined('__TATTERTOOLS_MOBILE__')) {
				$images = array_slice($attributes, 1, count($attributes) - 2);
				for ($i = 0; $i < count($images); $i++) {
					if (!empty($images[$i])) {
						if ($i % 2 == 0)
							$buf .= '<div>' . getAttachmentBinder($images[$i], '', $folderPath, $folderURL, 1, $useAbsolutePath) . '</div>';
						else
							$buf .= "<div>$images[$i]</div>";
					}
				}
			} else {
				$id = "Gallery$entryId$count";
				$items = array();
				for ($i = 1; $i < sizeof($attributes) - 2; $i += 2)
					array_push($items, array($attributes[$i], $attributes[$i + 1]));
				$galleryAttributes = getAttributesFromString($attributes[sizeof($attributes) - 1]);
				if ($useAbsolutePath && $count == 1)
					$buf .= '[HTML]' . printScript('gallery.js') . '[/HTML]';
				$buf .= '<div id="' . $id . '"></div>';
				$buf .= '<script type="text/javascript">var ' . $id . ' = new TTGallery("' . $id . '");';
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
						if ($useAbsolutePath)
							$buf .= $id . '.appendImage("' . "$hostURL{$service['path']}/attach/$owner/$item[0]" . '", "' . $item[1] . '", ' . intval($setWidth) . ', ' . intval($setHeight) . ");";
						else
							$buf .= $id . '.appendImage("' . "$folderURL/$item[0]" . '", "' . $item[1] . '", ' . intval($setWidth) . ', ' . intval($setHeight) . ");";
					}
				}
				$buf .= $id . '.show();</script>';
				$buf .= '<noscript><div style="text-align: center">';
				foreach ($items as $item) {
					$buf .= '<div>';
					if ($useAbsolutePath)
						$buf .= "<img src=\"$hostURL{$service['path']}/attach/$owner/$item[0]\" alt=\"\"/>";
					else
						$buf .= "<img src=\"$folderURL/$item[0]\" alt=\"\"/>";
					$buf .= '</div>';
					if(!empty($item[1]))
						$buf .= '<div>'.htmlspecialchars($item[1]).'</div>';
				}
				$buf .= '</div></noscript>';
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
					$caption = '<div class="cap1" style="text-align: center">' . $attributes[count($attributes) - 1] . '</div>';
				} else {
					$caption = '';
				}
				$buf .= '<center><img src="' . ($useAbsolutePath ? $hostURL : $service['path']) . '/image/gallery_enlarge.gif" width="70" height="19" alt="ZOOM" style="vertical-align: middle" onclick="openFullScreen(\'' . $service['path'] . '/script/gallery/iMazing/embed.php?d=' . urlencode($id) . '&f=' . urlencode($params['frame']) . '&t=' . urlencode($params['transition']) . '&n=' . urlencode($params['navigation']) . '&si=' . urlencode($params['slideshowInterval']) . '&p=' . urlencode($params['page']) . '&a=' . urlencode($params['align']) . '&o=' . $owner . '&i=' . $imgStr . '&r=' . $service['path'] . '\',\'' . str_replace("'", "\\'", $attributes[count($attributes) - 1]) . '\',\'' . $service['path'] . '\')" style="cursor:pointer; padding-bottom:10px"/>';
				$buf .= '<table>';
				$buf .= '<tr>';
				$buf .= '<td width="' . $params['width'] . '" height="' . $params['height'] . '">';
				$buf .= '<div id="iMazingContainer'.$id.'"></div><script type="text/javascript">iMazing' . $id . 'Str = getEmbedCode(\'' . $service['path'] . '/script/gallery/iMazing/main.swf\',\'100%\',\'100%\',\'iMazing' . $id . '\',\'#FFFFFF\',"image=' . $imgStr . '&amp;frame=' . $params['frame'] . '&amp;transition=' . $params['transition'] . '&amp;navigation=' . $params['navigation'] . '&amp;slideshowInterval=' . $params['slideshowInterval'] . '&amp;page=' . $params['page'] . '&amp;align=' . $params['align'] . '&amp;skinPath=' . $service['path'] . '/script/gallery/iMazing/&amp;","false"); writeCode(iMazing' . $id . 'Str, "iMazingContainer'.$id.'");</script><noscript>';
				for ($i = 0; $i < count($imgs); $i += 2)
					$buf .= '<img src="'.($useAbsolutePath ? $hostURL : $service['path']).'/attach/'.$owner.'/'.$imgs[$i].'" alt=""/>';
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
						echo "<a href=\"$folderURL/$sounds[$i]\">$sounds[$i]</a><br/>";
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
				$buf .= '<div id="jukeBox' . $id . 'Div" style="width:' . $width . '; height:' . $height . ';"><div id="jukeBoxContainer'.$id.'"></div>';
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
			switch (count($attributes)) {
				case 4:
					if (defined('__TATTERTOOLS_MOBILE__')) {
						$buf = '<div>' . getAttachmentBinder($attributes[1], $attributes[2], $folderPath, $folderURL, 1, $useAbsolutePath) . "</div><div>$attributes[3]</div>";
					} else {
						if (trim($attributes[3]) == '') {
							$marginBottom = '; margin-bottom: 10px';
							$caption = '';
						} else {
							$marginBottom = '';
							$caption = "<p class=\"cap1\" style=\"margin-top: 8px\">{$attributes[3]}</p>";
						}
						if ($attributes[0] == '1L') {
							$prefix = '<div class="imageblock left" style="float: left; margin-right: 10px' . $marginBottom . '">';
						} else if ($attributes[0] == '1C')
							$prefix = '<div class="imageblock center" style="text-align: center; clear: both' . $marginBottom . '">';
						else if ($attributes[0] == '1R')
							$prefix = '<div class="imageblock right" style="float: right; margin-left: 10px' . $marginBottom . '">';
						$buf = $prefix . getAttachmentBinder($attributes[1], $attributes[2], $folderPath, $folderURL, 1, $useAbsolutePath) . $caption . '</div>';
					}
					break;
				case 7:
					if (defined('__TATTERTOOLS_MOBILE__')) {
						$buf = '<div>' . getAttachmentBinder($attributes[1], $attributes[2], $folderPath, $folderURL, 1, $useAbsolutePath) . "</div><div>$attributes[3]</div>";
						$buf .= '<div>' . getAttachmentBinder($attributes[4], $attributes[5], $folderPath, $folderURL, 1, $useAbsolutePath) . "</div><div>$attributes[6]</div>";
					} else {
						$buf = '<div class="imageblock dual" style="text-align: center"><table style="margin: 0px auto" cellspacing="5"><tr><td>' . getAttachmentBinder($attributes[1], $attributes[2], $folderPath, $folderURL, 2, $useAbsolutePath) . "<div class=\"cap1\">{$attributes[3]}</div></td><td>" . getAttachmentBinder($attributes[4], $attributes[5], $folderPath, $folderURL, 2, $useAbsolutePath) . "<div class=\"cap1\">{$attributes[6]}</div></td></tr></table></div>";
					}
					break;
				case 10:
					if (defined('__TATTERTOOLS_MOBILE__')) {
						$buf = '<div>' . getAttachmentBinder($attributes[1], $attributes[2], $folderPath, $folderURL, 1, $useAbsolutePath) . "</div><div>$attributes[3]</div>";
						$buf .= '<div>' . getAttachmentBinder($attributes[4], $attributes[5], $folderPath, $folderURL, 1, $useAbsolutePath) . "</div><div>$attributes[6]</div>";
						$buf .= '<div>' . getAttachmentBinder($attributes[7], $attributes[8], $folderPath, $folderURL, 1, $useAbsolutePath) . "</div><div>$attributes[9]</div>";
					} else {
						$buf = '<div class="imageblock triple" style="text-align: center"><table style="margin: 0px auto" cellspacing="5"><tr><td>' . getAttachmentBinder($attributes[1], $attributes[2], $folderPath, $folderURL, 3, $useAbsolutePath) . "<div class=\"cap1\">{$attributes[3]}</div></td><td>" . getAttachmentBinder($attributes[4], $attributes[5], $folderPath, $folderURL, 3, $useAbsolutePath) . "<div class=\"cap1\">{$attributes[6]}</div></td><td>" . getAttachmentBinder($attributes[7], $attributes[8], $folderPath, $folderURL, 3, $useAbsolutePath) . "<div class=\"cap1\">{$attributes[9]}</div></td></tr></table></div>";
					}
					break;
			}
		}
		$view = substr($view, 0, $start) . $buf . substr($view, $end + 4);
	}
	return $view;
}

function getAttachmentBinder($filename, $property, $folderPath, $folderURL, $imageBlocks = 1, $useAbsolutePath = false) {
	global $service, $owner, $blogURL, $hostURL;
	$path = "$folderPath/$filename";
	if ($useAbsolutePath)
		$url = "$hostURL{$service['path']}/attach/$owner/$filename";
	else
		$url = "$folderURL/$filename";
	$fileInfo = getAttachmentByOnlyName($owner, $filename);
	switch (getFileExtension($filename)) {
		case 'jpg':case 'jpeg':case 'gif':case 'png':case 'bmp':
			if (defined('__TATTERTOOLS_MOBILE__')) {
				return fireEvent('ViewAttachedImageMobile', "<img src=\"$blogURL/imageResizer/?f=" . urlencode($filename) . "\" alt=\"\"/>", $path);
			} else {
				list($width, $height) = @getimagesize($path);
				$setWidth = $width;
				$setHeight = $height;
				$scroll = 0;
				if ($width > 800) {
					$setWidth = 820;
					$scroll = 1;
				}
				if ($height > 600) {
					$setWidth += 10;
					$setHeight = 600;
					$scroll = 1;
				}
				$property = str_replace('&quot;', '"', $property);
				if (strpos(str_replace('"', '', $property), "width=$width") !== false && strpos(str_replace('"', '', $property), "height=$height") !== false)
					return fireEvent('ViewAttachedImage', "<img src=\"$url\" $property/>", $path);
				else {
					$setWidth += 50;
					$setHeight += 150;
					return fireEvent('ViewAttachedImage', "<img src=\"$url\" $property style=\"cursor: pointer\" onclick=\"open_img('$url')\"/>", $path);
				}
			}
			break;
		case 'swf':
			$id = md5($url) . rand(1, 10000);
			return "<span id=\"$id\"></span>
			<script type=\"text/javascript\">writeCode(getEmbedCode('$url','300','400','$id','#FFFFFF',''), \"$id\");</script>";
			break;
		case 'wmv':case 'avi':case 'asf':case 'mpg':case 'mpeg':
			$id = md5($url) . rand(1, 10000);
			return "<span id=\"$id\"></span><script type=\"text/javascript\">writeCode('<embed $property autostart=\"0\" src=\"$url\"></embed>', \"$id\")</script>";
			break;
		case 'mp3':case 'mp2':case 'wma':case 'wav':case 'mid':case 'midi':
			$id = md5($url) . rand(1, 10000);
			return "<span id=\"$id\"></span><script type=\"text/javascript\">writeCode('<embed $property autostart=\"0\" height=\"45\" src=\"$url\"></embed>', \"$id\")</script>";
			break;
		case 'mov':
			$id = md5($url) . rand(1, 10000);
			return "<span id=\"$id\"></span><script type=\"text/javascript\">writeCode(" . '\'<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="400" height="300"><param name="src" value="' . $url . '" /><param name="controller" value="true" /><param name="pluginspage" value="http://www.apple.com/QuickTime/download/" /><!--[if !IE]> <--><object type="video/quicktime" data="' . $url . '" width="400" height="300" class="mov"><param name="controller" value="true" /><param name="pluginspage" value="http://www.apple.com/QuickTime/download/" /></object><!--> <![endif]--></object>\'' . ", \"$id\")</script>";
			break;
		default:
			if (file_exists(ROOT . '/image/' . getFileExtension($filename) . '.gif')) {
				return '<a href="' . ($useAbsolutePath ? $hostURL : '') . $blogURL . '/attachment/' . $filename . '"><img src="' . ($useAbsolutePath ? $hostURL : '') . $service['path'] . '/image/' . getFileExtension($filename) . '.gif" alt="" align=""/> ' . htmlspecialchars($fileInfo['label']) . '</a>';
			} else {
				return '<a href="' . ($useAbsolutePath ? $hostURL : '') . $blogURL . '/attachment/' . $filename . '"><img src="' . ($useAbsolutePath ? $hostURL : '') . $service['path'] . '/image/unknown.gif" alt="" align="bottom"/> ' . htmlspecialchars($fileInfo['label']) . '</a>';
			}
			break;
	}
}

function printFeedGroups($owner, $selectedGroup = 0, $starredOnly = false, $searchKeyword = null) {
	global $service;
	echo '<table id="groupList" width="217" border="0" cellspacing="0" cellpadding="3" style="margin: 10px; table-layout: fixed">';
	foreach (getFeedGroups($owner, $starredOnly, $searchKeyword) as $group) {
		if ($group['id'] == 0)
			$group['title'] = _t('전체보기');
		$highlight = ($selectedGroup == $group['id']) ? ' style="background-color: #CDE3FF"' : '';
?>
		<tr id="groupList<?=$group['id']?>" height="20" groupid="<?=$group['id']?>"<?=$highlight?>>
			<td class="pointerCursor" onclick="Reader.selectGroup(this, <?=$group['id']?>)"><img src="<?=$service['path']?>/image/owner/reader/iconCategory<?=$group['id'] ? 'Open' : 'T'?>.gif" /> <?=htmlspecialchars($group['title'])?></td>
			<td align="right" width="30"><?
		if ($group['id']) {
?><img class="pointerCursor" src="<?=$service['path']?>/image/owner/reader/btnModify.gif" onclick="Reader.editGroup(<?=$group['id']?>, '<?=$group['title']?>')"/><?
		}
?></td>
		</tr>
		<tr height="1">
			<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline.gif"></td>
		</tr>
		<?
	}
?>
	</table>
	<table id="groupAdder" width="217" border="0" cellspacing="0" cellpadding="3" style="margin: 30px 10px 10px">
		<tr>
			<td align="center">
				<input id="newGroupTitle" type="text" class="text2" value="<?=_t('카테고리를 추가하세요')?>" style="border:1px #999 solid; width: 150px" onfocus="if(this.value == '<?=_t('카테고리를 추가하세요')?>') this.value = ''" onkeydown="if(event.keyCode==13) Reader.addGroup(this.value)"/>
				<input type="button" value="<?=_t('추가')?>" style="border: 1px #999 solid; background: #ddd; font-size: 11px;padding-top:2px" onclick="Reader.addGroup(document.getElementById('newGroupTitle').value)"/>
			</td>
		</tr>
	</table>
	<div id="groupEditor" style="display: none">
		<div style="font-size:14px; font-weight:bold; margin: 10px 0px 0px 30px"><?=_t('카테고리 수정하기')?></div>
		<table align="center" width="80%" border="0" cellpadding="0" cellspacing="0" bgcolor="#e3effe">
			<tr>
				<td align="center" style="padding:15px 10px">
					<input id="changeGroupTitle" type="text" class="text2" style="border:1px #999 solid;height:20px; width:180px" />
					<input type="button" value="<?=_t('수정하기')?>" style="border:1px #5788C4 solid; background:#8DB0DC;padding-top:2px;color:#fff; margin-top:5px" onclick="Reader.editGroupExecute()"/>
					<input type="button" value="<?=_t('삭제하기')?>" style="border:1px #5788C4 solid; background:#8DB0DC;padding-top:2px;color:#fff; margin-top:5px" onclick="Reader.deleteGroup()"/>
					<input type="button" value="<?=_t('취소하기')?>" style="border:1px #5788C4 solid; background:#8DB0DC;padding-top:2px;color:#fff; margin-top:5px" onclick="Reader.cancelEditGroup()"/>
				</td>
			</tr>
		</table>
	</div>
<?
}

function printFeeds($owner, $group = 0, $starredOnly = false, $searchKeyword = null) {
	global $service;
	echo '<table id="feedList" border="0" cellspacing="0" cellpadding="3" style="margin: 10px; table-layout: fixed">';
	foreach (getFeeds($owner, $group, $starredOnly, $searchKeyword) as $feed) {
		if ($feed['modified'] > time() - 86400)
			$status = 'Update';
		else if ($feed['modified'] == 0)
			$status = 'Failure';
		else
			$status = 'UpdateNo';
?>
		<tr height="20" feedid="<?=$feed['id']?>">
			<td class="pointerCursor overflowCell" onclick="Reader.selectFeed(this, <?=$feed['id']?>)"><img id="iconFeedStatus<?=$feed['id']?>" class="pointerCursor" src="<?=$service['path']?>/image/owner/reader/icon<?=$status?>.gif" width="10" height="10" alt="Refresh this feed" onclick="Reader.updateFeed(<?=$feed['id']?>); event.cancelBubble=true;return false"/> <?=$feed['blogURL'] ? '<a href="' . htmlspecialchars($feed['blogURL']) . '" onclick="window.open(this.href); event.cancelBubble=true; return false">' : ''?><strong><?=htmlspecialchars($feed['title'])?></strong><?=$feed['blogURL'] ? '</a>' : ''?> <span style="color: #888" title="<?=escapeJSInAttribute($feed['description'])?>"><?=$feed['description']?'| ':''?><?=htmlspecialchars($feed['description'])?></span></td>
			<td align="right" width="30"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/reader/btnModify.gif" onclick="Reader.editFeed(<?=$feed['id']?>, '<?=htmlspecialchars($feed['xmlURL'])?>')"/></td>
		</tr>
		<tr height="1">
			<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline.gif"></td>
		</tr>
		<?
	}
?>
	</table>
	<table id="feedAdder" border="0" cellspacing="0" cellpadding="3" style="margin-top: 30px">
		<tr>
			<td align="center">
				<input id="newFeedURL" type="text" class="text2" value="<?=_t('피드 주소를 입력하세요')?>" style="border:1px #999 solid; width:480px" onkeydown="if(event.keyCode==13) Reader.addFeed(this.value)" onfocus="if(this.value == '<?=_t('피드 주소를 입력하세요')?>') this.value = ''"/>
				<input type="button" value="<?=_t('추가')?>" style="border: 1px #999 solid; background: #ddd; font-size: 11px;padding-top:2px" onclick="Reader.addFeed(document.getElementById('newFeedURL').value)"/>
				<?=fireEvent('AddFeedURLToolbox', '')?>
			</td>
		</tr>
	</table>
	<div id="feedEditor" style="display: none">
		<div style="font-size:14px; font-weight:bold; margin: 10px 0px 0px 30px"><?=_t('피드 수정하기')?></div>
		<table align="center" width="90%" border="0" cellpadding="0" cellspacing="0" bgcolor="#e3effe">
			<tr>
				<td align="center" style="padding:15px 10px;">
					<select id="changeFeedGroup" style="width: 30%">
					<?
	foreach (getFeedGroups($owner) as $group) {
		if ($group['id'] == 0)
			$group['title'] = _t('그룹 없음');
?>
					<option value="<?=$group['id']?>"><?=htmlspecialchars($group['title'])?></option>
					<?
	}
?>
					</select>
					<input id="changeFeedURL" class="text2" type="text" style="border:1px #999 solid; width: 60%" disabled="disabled" /><br/>
					<input type="button" value="<?=_t('수정하기')?>" style="border:1px #5788C4 solid;background:#8DB0DC;padding-top:2px;color:#fff; margin-top:5px;" onclick="Reader.editFeedExecute()"/>
					<input type="button" value="<?=_t('삭제하기')?>" style="border:1px #5788C4 solid;background:#8DB0DC;padding-top:2px;color:#fff; margin-top:5px;" onclick="Reader.deleteFeed()"/>
					<input type="button" value="<?=_t('취소하기')?>" style="border:1px #5788C4 solid;background:#8DB0DC;padding-top:2px;color:#fff; margin-top:5px;" onclick="Reader.cancelEditFeed()"/>
				</td>
			</tr>
		</table>
	</div>
	<?
}

function printFeedEntries($owner, $group = 0, $feed = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null) {
	global $service;
	echo '<table width="232" border="0" cellpadding="0" cellspacing="0">';
	$count = 0;
	foreach (getFeedEntries($owner, $group, $feed, $unreadOnly, $starredOnly, $searchKeyword) as $entry) {
		$count++;
		if ($count == 1)
			$firstEntryId = $entry['id'];
		$class = $entry['wasread'] ? 'read' : 'unread';
		$starred = $entry['item'] ? 'On' : 'Off';
		$podcast = $entry['enclosure'] ? '<img src="' . $service['path'] . '/image/owner/reader/iconPodcast.gif" vspace="4" />' : '';
?>
		<tr entryid="<?=$entry['id']?>">
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-left: 6px">
					<tr<?=($count == 1) ? ' style="background-color: #fff"' : ''?>>
						<td width="16" valign="top" style="padding: 6px 0px"><img id="star<?=$entry['id']?>" class="pointerCursor" src="<?=$service['path']?>/image/owner/reader/iconStar<?=$starred?>.gif" starred="<?=$starred?>" onclick="Reader.toggleStarred(<?=$entry['id']?>)"/><br/><?=$podcast?></td>
						<td id="entryTitleList<?=$entry['id']?>" class="<?=$class?>" onclick="Reader.selectEntry(<?=$entry['id']?>)" style="padding:6px 0px; cursor: pointer; word-break: break-all"><span><?=htmlspecialchars($entry['entry_title'])?></span><br /><?=htmlspecialchars($entry['blog_title'])?></td>
					</tr>
					<tr height="1">
						<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline02.gif"></td>
					</tr>
				</table>
			</td>
		</tr>
		<?
	}
?>
	</table>
	<div id="additionalFeedContainer"></div>
	<div id="feedLoadingIndicator" style="background-color: #b5e1f4; text-align: center; border: 2px solid #a4d8eb; color: #1d5d81; padding: 8px 5px 5px; margin: 10px; font-size: 10px; display: none">
		<img src="<?=$service['path']?>/image/owner/reader/feedLoading.gif" style="vertical-align: 0%; margin-right: 5px"/>
		<?=_t('피드를 읽어오고 있습니다')?>
	</div>
	<script type="text/javascript">
	//<![CDATA[
		Reader.setShownEntries(<?=$count?>);
		Reader.setTotalEntries(<?=getFeedEntriesTotalCount($owner, $group, $feed, $unreadOnly, $starredOnly, $searchKeyword)?>);
<?
	if (isset($firstEntryId)) {
?>
		Reader.selectedEntryObject = document.getElementById("entryTitleList<?=$firstEntryId?>").parentNode;
<?
	}
?>
	//]]>
	</script>
	<?
	return $count;
}

function printFeedEntriesMore($owner, $group = 0, $feed = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null, $offset) {
	global $service;
	echo '<table width="232" border="0" cellpadding="0" cellspacing="0">';
	$count = 0;
	foreach (getFeedEntries($owner, $group, $feed, $unreadOnly, $starredOnly, $searchKeyword, $offset) as $entry) {
		$count++;
		$class = $entry['wasread'] ? 'read' : 'unread';
		$starred = $entry['item'] ? 'On' : 'Off';
		$podcast = $entry['enclosure'] ? '<img src="' . $service['path'] . '/image/owner/reader/iconPodcast.gif" vspace="4" />' : '';
?>
		<tr entryid="<?=$entry['id']?>">
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-left: 6px">
					<tr>
						<td width="16" valign="top" style="padding: 6px 0px"><img id="star<?=$entry['id']?>" class="pointerCursor" src="<?=$service['path']?>/image/owner/reader/iconStar<?=$starred?>.gif" starred="<?=$starred?>" onclick="Reader.toggleStarred(<?=$entry['id']?>)"/><br/><?=$podcast?></td>
						<td id="entryTitleList<?=$entry['id']?>" class="<?=$class?>" onclick="Reader.selectEntry(<?=$entry['id']?>)" style="padding:6px 0px; cursor: pointer"><span><?=htmlspecialchars($entry['entry_title'])?></span><br /><?=htmlspecialchars($entry['blog_title'])?></td>
					</tr>
					<tr height="1">
						<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline02.gif"></td>
					</tr>
				</table>
			</td>
		</tr>
		<?
	}
?>
	</table>
	<?
	return $count;
}

function printFeedEntry($owner, $group = 0, $feed = 0, $entry = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null, $position = 'current') {
	global $service;
	if (!$entry = getFeedEntry($owner, $group, $feed, $entry, $unreadOnly, $starredOnly, $searchKeyword, $position)) {
		$entry = array('id' => 0, 'author' => 'Tattertools', 'blog_title' => 'Tattertools Reader', 'permalink' => '#', 'entry_title' => _t('포스트가 없습니다'), 'language' => 'en-US', 'description' => '<div style="height: 369px"></div>', 'tags' => '', 'enclosure' => '', 'written' => time());
	}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="10">
   <tr>
	<td>
	  <table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td><a href="<?=htmlspecialchars($entry['permalink'])?>" target="_blank" style="text-decoration: none"><span style="color:#0047B6; font-size:16px; font-weight:bold"><?=htmlspecialchars($entry['entry_title'])?></span></a><br />
			  by <?=htmlspecialchars($entry['author'] ? $entry['author'] : $entry['blog_title'])?> : <span style="font-size:10px; font-family:Tahoma"><?=date('Y-m-d H:i:s', $entry['written'])?></span> </td>
			<td align="right" valign="top"><a id="entryPermalink" href="<?=htmlspecialchars($entry['permalink'])?>" target="_blank"><img src="<?=$service['path']?>/image/owner/reader/viewNewwindow.gif" align="absmiddle" /><?=_t('새 창으로 보기')?></a></td>
		  </tr>
		  <tr height="1">
			<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline.gif"></td>
		  </tr>
		</table>
		  <table width="100%" border="0" cellspacing="0">
			<tr>
			  <td id="entryBody" style="padding: 10px 0px" lang="<?=htmlspecialchars($entry['language'])?>" xml:lang="<?=htmlspecialchars($entry['language'])?>">
			  	<?
	if ($entry['enclosure']) {
		if (preg_match('/\.mp3$/i', $entry['enclosure'])) {
?>
						<p><img src="<?=$service['path']?>/image/owner/reader/iconPodcast.gif" style="vertical-align: 0%"/>
				<a href="<?=htmlspecialchars($entry['enclosure'])?>"><?=htmlspecialchars($entry['enclosure'])?></a></p>
						<?
		} else {
?>
						<p><img src="<?=$service['path']?>/image/owner/reader/iconPodcast.gif" style="vertical-align: 0%"/>
				<a href="<?=htmlspecialchars($entry['enclosure'])?>"><?=htmlspecialchars($entry['enclosure'])?></a></p>
						<?
		}
	}
?>
				<?=$entry['description']?>
			  </td>
			  </tr>
		  </table>
		</td>
	</tr>
  </table>
	<script type="text/javascript">
	//<![CDATA[
		Reader.selectedEntry = <?=escapeJSInAttribute($entry['id'])?>;
		Reader.setBlogTitle('<?=escapeJSInAttribute($entry['blog_title'])?>');
		Reader.doPostProcessingOnEntry();
	//]]>
	</script>
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
	  <tr height="1">
		<td bgcolor="#dddddd"></td>
	  </tr>
	  <tr>
		<td bgcolor="#f5f5f5" style="padding: 10px">
		<?
	if ($entry['tags'])
		echo '<span style="color:#0047B6">' . htmlspecialchars(_t('태그')) . ' : ' . htmlspecialchars($entry['tags']) . '</span>';
?>
		</td>
	  </tr>
	  <tr>
		<td align="right" bgcolor="#f5f5f5" style="padding: 10px">
			<span class="pointerCursor" style="border:1px #999 solid;background:#fff;font-size:11px; height:18px; padding: 4px 1px 1px 4px" onclick="Reader.markAsUnread(<?=$entry['id']?>)">
				<?=_t('안읽은 글로 표시')?>
			</span>
		</td>
	  </tr>
  </table>	
	</td>
  </tr>
</table>
	<?
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
?>