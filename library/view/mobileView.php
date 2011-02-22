<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function printMobileEntryContentView($blogid, $entry, $keywords = array()) {
	if (doesHaveOwnership() || ($entry['visibility'] >= 2) || (isset($_COOKIE['GUEST_PASSWORD']) && (trim($_COOKIE['GUEST_PASSWORD']) == trim($entry['password']))))
		print (getEntryContentView($blogid, $entry['id'], $entry['content'], $entry['contentformatter'], $keywords));
	else
	{
	?>
	<p><?php echo _text('보호된 글입니다');?></p>
	<form method="post" action="protected/<?php echo $entry['id'];?>">
		<div>
		<label for="password"><?php echo _text('비밀번호');?></label>
		<input type="password" id="password" name="password" />
		<input type="submit" value="<?php echo _text('내용보기');?>" />
		</div>
	</form>
	<?php
	}
}

function printMobileHtmlHeader($title = '') {
	global $blogURL, $blog;
	$title = htmlspecialchars($blog['title']) . ' :: ' . $title;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title><?php echo $title;?></title>
	</head>
	<body>
		<div id="header">
			<h1><a href="<?php echo $blogURL;?>" accesskey="0"><?php echo htmlspecialchars($blog['title']);?></a></h1>
		</div>
		<hr />
<?php
}

function printMobileHtmlFooter() {
?>
		<hr />
		<p>Powered by <a href="<?php echo TEXTCUBE_HOMEPAGE;?>"><?php echo TEXTCUBE_NAME;?></a></p>
	</body>
</html>
<?php
}

function printMobileNavigation($entry, $jumpToComment = true, $jumpToTrackback = true, $paging = null) {
	global $suri, $blogURL;
?>
<hr />
<div id="navigation">
	<ul>
		<?php
	if (isset($paging['prev'])) {
?>
		<li><a href="<?php echo $blogURL;?>/<?php echo $paging['prev'];?>" accesskey="1"><?php echo _text('이전 글 보기');?></a></li>
		<?php
	}
	if (isset($paging['next'])) {
?>
		<li><a href="<?php echo $blogURL;?>/<?php echo $paging['next'];?>" accesskey="2"><?php echo _text('다음 글 보기');?></a></li>
		<?php
	}
	if (!isset($paging)) {
?>	
		<li><a href="<?php echo $blogURL;?>/<?php echo $entry['id'];?>" accesskey="3"><?php echo _text('포스트보기');?></a></li>
		<?php
	}
	if ($jumpToComment) {
?>
		<li><a href="<?php echo $blogURL;?>/comment/<?php echo $entry['id'];?>" accesskey="4"><?php echo _text('댓글 보기');?> (<?php echo $entry['comments'];?>)</a></li>
		<?php
	}
	if ($jumpToTrackback) {
?>
		<li><a href="<?php echo $blogURL;?>/trackback/<?php echo $entry['id'];?>" accesskey="5"><?php echo _text('걸린 글 보기');?> (<?php echo $entry['trackbacks'];?>)</a></li>
		<?php
	}
	if ($suri['directive'] != '/m/pannels') {
?>
		<li><a href="<?php echo $blogURL;?>/pannels/<?php echo $entry['id'];?>" accesskey="6"><?php echo _text('다른 메뉴보기');?></a></li>
		<?php
	}
?>
	</ul>
</div>
<?php
}

function printMobileTrackbackView($entryId) {
	$trackbacks = getTrackbacks($entryId);
	if (count($trackbacks) == 0) {
?>
		<div class="trackback">
			<?php echo _text('걸린 글이 없습니다');?>
		</div>
		<?php
	} else {
		foreach (getTrackbacks($entryId) as $trackback) {
?>
		<div class="trackback">
			<div class="name">
				<strong><?php echo htmlspecialchars($trackback['subject']);?></strong>
				(<?php echo Timestamp::format5($trackback['written']);?>)
			</div>
			<div class="body"><?php echo htmlspecialchars($trackback['excerpt']);?></div>
		</div>
		<hr />
		<?php
		}
	}
}

function printMobileCommentView($entryId) {
	global $blogURL;
	$comments = getComments($entryId);
	if (count($comments) == 0) {
?>
		<div class="comment">
			<?php echo _text('댓글이 없습니다');?>
		</div>
		<hr />
		<?php
	} else {
		foreach ($comments as $commentItem) {
?>
		<div class="comment">
			<div class="name">
				<?php if(!empty($commentItem['name'])) { ?><strong><?php echo htmlspecialchars($commentItem['name']);?></strong><?php } ?>
				<a href="<?php echo $blogURL;?>/comment/comment/<?php echo $commentItem['id'];?>">RE</a>
				<a href="<?php echo $blogURL;?>/comment/delete/<?php echo $commentItem['id'];?>">DEL</a><br />
				(<?php echo Timestamp::format5($commentItem['written']);?>)
			</div>
			<div class="body"><?php echo ($commentItem['secret'] && doesHaveOwnership() ? '<div class="hiddenComment" style="font-weight: bold; color: #e11">'._t('비밀 댓글').' &gt;&gt;</div>' : '').nl2br(addLinkSense(htmlspecialchars($commentItem['comment'])));?></div>
			<?php
			foreach (getCommentComments($commentItem['id']) as $commentSubItem) {
?>
			<blockquote>
				<div class="name">
					<?php if(!empty($commentSubItem['name'])) { ?><strong><?php echo htmlspecialchars($commentSubItem['name']);?></strong><?php } ?>
					<a href="<?php echo $blogURL;?>/comment/delete/<?php echo $commentSubItem['id'];?>">DEL</a><br />
					(<?php echo Timestamp::format5($commentSubItem['written']);?>)
				</div>
				<div class="body"><?php echo ($commentSubItem['secret'] && doesHaveOwnership() ? '<div class="hiddenComment" style="font-weight: bold; color: #e11">'._t('비밀 댓글').' &gt;&gt;</div>' : '').nl2br(addLinkSense(htmlspecialchars($commentSubItem['comment'])));?></div>
			</blockquote>
			<?php
			}
?>
		</div>
		<hr />
		<?php
		}
	}
	printMobileCommentFormView($entryId);
}

function printMobileCommentFormView($entryId) {
?>
	<fieldset>
	<form method="post" action="add/<?php echo $entryId;?>">	
		<?php
	if (!doesHaveOwnership()) {
?>
		<input type="hidden" name="id" value="<?php echo $entryId;?>" />
		<label for="secret_<?php echo $entryId;?>"><?php echo _text('비밀글로 등록');?></label>
		<input type="checkbox" id="secret_<?php echo $entryId;?>" name="secret_<?php echo $entryId;?>" />
		<br />
		<label for="name_<?php echo $entryId;?>"><?php echo _text('이름');?></label>
		<input type="text" id="name_<?php echo $entryId;?>" name="name_<?php echo $entryId;?>" value="<?php echo isset($_COOKIE['guestName']) ? htmlspecialchars($_COOKIE['guestName']) : '';?>" />
		<br />
		<label for="password_<?php echo $entryId;?>"><?php echo _text('비밀번호');?></label>
		<input type="password" id="password_<?php echo $entryId;?>" name="password_<?php echo $entryId;?>" />
		<br />
		<label for="homepage_<?php echo $entryId;?>"><?php echo _text('홈페이지');?></label>
		<input type="text" id="homepage_<?php echo $entryId;?>" name="homepage_<?php echo $entryId;?>"  value="<?php echo (isset($_COOKIE['guestHomepage']) && $_COOKIE['guestHomepage'] != 'http://') ? htmlspecialchars($_COOKIE['guestHomepage']) : 'http://';?>" />
		<br />
		<?php
	}
?>
		<label for="comment_<?php echo $entryId;?>"><?php echo _text('내용');?></label>
		<textarea cols="40" rows="5" id="comment_<?php echo $entryId;?>" name="comment_<?php echo $entryId;?>"></textarea>
		<br />
		<input type="submit" value="<?php echo _text('등록');?>" />
	</form>
	</fieldset>
	<?php
}

function printMobileErrorPage($messageTitle, $messageBody, $redirectURL) {
	printMobileHtmlHeader('Error');
?>
<h2><?php echo htmlspecialchars($messageTitle);?></h2>
<p><?php echo htmlspecialchars($messageBody);?></p>
<a href="<?php echo $redirectURL;?>"><?php echo _text('이전 페이지로');?></a>
<?php
	printMobileHtmlFooter();
}

function printMobileSimpleMessage($message, $redirectMessage, $redirectURL, $title = '') {
	printMobileHtmlHeader($title);
?>
<h2><?php echo htmlspecialchars($message);?></h2>
<a href="<?php echo $redirectURL;?>"><?php echo htmlspecialchars($redirectMessage);?></a>
<?php
	printMobileHtmlFooter();
}
?>
