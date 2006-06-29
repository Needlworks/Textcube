<?
function printMobileEntryContentView($owner, $entry, $keywords = array()) {
	if (doesHaveOwnership() || ($entry['visibility'] >= 2) || (isset($_COOKIE['GUEST_PASSWORD']) && ($_COOKIE['GUEST_PASSWORD'] == $entry['password'])))
		print (getEntryContentView($owner, $entry['id'], $entry['content'], $keywords));
	else
	{
	?>
	<p><?=_t('보호된 글입니다')?></p>
	<form method="post" action="protected/<?=$entry['id']?>">
		<div>
		<label for="password"><?=_t('비밀번호')?></label>
		<input type="password" id="password" name="password"/>
		<input type="submit" value="<?=_t('내용 보기')?>"/>
		</div>
	</form>
	<?
	}
}

function printMobileHtmlHeader($title = '') {
	global $blogURL, $blog;
	$title = htmlspecialchars($blog['title']) . ' :: ' . $title;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title><?=$title?></title>
	</head>
	<body>
		<div id="header">
			<h1><a href="<?=$blogURL?>" accesskey="0"><?=htmlspecialchars($blog['title'])?></a></h1>
		</div>
		<hr/>
<?
}

function printMobileHtmlFooter() {
?>
		<hr/>
		<p>Powered by <a href="http://www.tattertools.com">Tattertools</a></p>
	</body>
</html>
<?
}

function printMobileNavigation($entry, $jumpToComment = true, $jumpToTrackback = true, $paging = null) {
	global $suri, $blogURL;
?>
<hr/>
<div id="navigation">
	<ul>
		<?
	if (isset($paging['prev'])) {
?>
		<li><a href="<?=$blogURL?>/<?=$paging['prev']?>" accesskey="1"><?=_t('이전 글 보기')?></a></li>
		<?
	}
	if (isset($paging['next'])) {
?>
		<li><a href="<?=$blogURL?>/<?=$paging['next']?>" accesskey="2"><?=_t('다음 글 보기')?></a></li>
		<?
	}
	if (!isset($paging)) {
?>	
		<li><a href="<?=$blogURL?>/<?=$entry['id']?>" accesskey="3"><?=_t('포스트 보기')?></a></li>
		<?
	}
	if ($jumpToComment) {
?>
		<li><a href="<?=$blogURL?>/comment/<?=$entry['id']?>" accesskey="4"><?=_t('답글 보기')?> (<?=$entry['comments']?>)</a></li>
		<?
	}
	if ($jumpToTrackback) {
?>
		<li><a href="<?=$blogURL?>/trackback/<?=$entry['id']?>" accesskey="5"><?=_t('트랙백 보기')?> (<?=$entry['trackbacks']?>)</a></li>
		<?
	}
	if ($suri['directive'] != '/m/pannels') {
?>
		<li><a href="<?=$blogURL?>/pannels/<?=$entry['id']?>" accesskey="6"><?=_t('다른 메뉴 보기')?></a></li>
		<?
	}
?>
	</ul>
</div>
<?
}

function printMobileTrackbackView($entryId) {
	$trackbacks = getTrackbacks($entryId);
	if (count($trackbacks) == 0) {
?>
		<div class="trackback">
			<?=_t('트랙백이 없습니다')?>
		</div>
		<?
	} else {
		foreach (getTrackbacks($entryId) as $trackback) {
?>
		<div class="trackback">
			<div class="name">
				<strong><?=htmlspecialchars($trackback['subject'])?></strong>
				(<?=Timestamp::format5($trackback['written'])?>)
			</div>
			<div class="body"><?=htmlspecialchars($trackback['excerpt'])?></div>
		</div>
		<hr/>
		<?
		}
	}
}

function printMobileCommentView($entryId) {
	global $blogURL;
	$comments = getComments($entryId);
	if (count($comments) == 0) {
?>
		<div class="comment">
			<?=_t('답글이 없습니다')?>
		</div>
		<hr/>
		<?
	} else {
		foreach ($comments as $commentItem) {
?>
		<div class="comment">
			<div class="name">
				<strong><?=htmlspecialchars($commentItem['name'])?></strong>
				<a href="<?=$blogURL?>/comment/comment/<?=$commentItem['id']?>">RE</a>
				<a href="<?=$blogURL?>/comment/delete/<?=$commentItem['id']?>">DEL</a><br/>
				(<?=Timestamp::format5($commentItem['written'])?>)
			</div>
			<div class="body"><?=nl2br(addLinkSense(htmlspecialchars($commentItem['comment'])))?></div>
			<?
			foreach (getCommentComments($commentItem['id']) as $commentSubItem) {
?>
			<blockquote>
				<div class="name">
					<strong><?=htmlspecialchars($commentSubItem['name'])?></strong>
					<a href="<?=$blogURL?>/comment/delete/<?=$commentSubItem['id']?>">DEL</a><br/>
					(<?=Timestamp::format5($commentSubItem['written'])?>)
				</div>
				<div class="body"><?=nl2br(addLinkSense(htmlspecialchars($commentSubItem['comment'])))?></div>
			</blockquote>
			<?
			}
?>
		</div>
		<hr/>
		<?
		}
	}
	printMobileCommentFormView($entryId);
}

function printMobileCommentFormView($entryId) {
?>
	<fieldset>
	<form method="post" action="add/<?=$entryId?>">	
		<?
	if (!doesHaveOwnership()) {
?>
		<input type="hidden" name="id" value="<?=$entryId?>"/>
		<label for="secret_<?=$entryId?>"><?=_t('비밀글로 등록')?></label>
		<input type="checkbox" id="secret_<?=$entryId?>" name="secret_<?=$entryId?>"/>
		<br/>
		<label for="name_<?=$entryId?>"><?=_t('이름')?></label>
		<input type="text" id="name_<?=$entryId?>" name="name_<?=$entryId?>" value="<?=isset($_COOKIE['guestName']) ? htmlspecialchars($_COOKIE['guestName']) : ''?>"/>
		<br/>
		<label for="password_<?=$entryId?>"><?=_t('비밀번호')?></label>
		<input type="password" id="password_<?=$entryId?>" name="password_<?=$entryId?>"/>
		<br/>
		<label for="homepage_<?=$entryId?>"><?=_t('홈페이지')?></label>
		<input type="text" id="homepage_<?=$entryId?>" name="homepage_<?=$entryId?>" value="<?=(isset($_COOKIE['guestHomepage']) && $_COOKIE['guestHomepage'] != 'http://') ? htmlspecialchars($_COOKIE['guestHomepage']) : 'http://'?>"/>
		<br/>
		<?
	}
?>
		<label for="comment_<?=$entryId?>"><?=_t('내용')?></label>
		<textarea rows="5" id="comment_<?=$entryId?>" name="comment_<?=$entryId?>"></textarea>
		<br/>
		<input type="submit" value="<?=_t('등록')?>"/>
	</form>
	</fieldset>
	<?
}

function printMobileErrorPage($messageTitle, $messageBody, $redirectURL) {
	printMobileHtmlHeader('Error');
?>
<h2><?=htmlspecialchars($messageTitle)?></h2>
<p><?=htmlspecialchars($messageBody)?></p>
<a href="<?=$redirectURL?>"><?=_t('이전 페이지로')?></a>
<?
	printMobileHtmlFooter();
}

function printMobileSimpleMessage($message, $redirectMessage, $redirectURL, $title = '') {
	printMobileHtmlHeader($title);
?>
<h2><?=htmlspecialchars($message)?></h2>
<a href="<?=$redirectURL?>"><?=htmlspecialchars($redirectMessage)?></a>
<?
	printMobileHtmlFooter();
}
?>