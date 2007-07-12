<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title>Textcube Data Correcting</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript">
		//<![CDATA[
			var pi = window.parent.document.getElementById("correctingIndicator");
			var pt = window.parent.document.getElementById("correctingText");
			var pts = window.parent.document.getElementById("correctingTextSub");
		//]]>
	</script>
</head>
<body>
<?php
function finish($error = null) {
?>
	<script type="text/javascript">
		//<![CDATA[
<?php
	if ($error) {
?>
			alert("<?php echo $error;?>");
<?php
	} else {
?>
			alert("<?php echo _t('성공적으로 교정되었습니다.');?>");
<?php
	}
?>
			window.parent.document.getElementById("correctingDataDialog").style.display = "none";
			window.parent.document.getElementById("correctingDataDialogTitle").innerHTML = "";
			window.parent.document.getElementById("correctingText").innerHTML = "";
			window.parent.document.getElementById("correctingTextSub").innerHTML = "";
		//]]>
	</script>
	<?php echo _t('완료.');?>
</body>
</html>
<?php
	exit;
}
$lastProgress = 0;
$lastProgressText = null;
$lastProgressTextSub = null;

function setProgress($progress, $text = null, $sub = null) {
	global $lastProgress, $lastProgressText, $lastProgressTextSub;
	$progress = intval($progress);
	$diff = '';
	if (isset($progress) && ($progress != $lastProgress)) {
		$lastProgress = $progress;
		$diff .= 'pi.style.width = "' . $progress . '%";';
	}
	if (isset($text) && ($text != $lastProgressText)) {
		$lastProgressText = $text;
		$diff .= 'pt.innerHTML = "' . $text . '";';
		if (!isset($sub)) {
			$lastProgressTextSub = '';
			$diff .= 'pts.innerHTML = "";';
		}
	}
	if (isset($sub) && ($sub != $lastProgressTextSub)) {
		$lastProgressTextSub = $sub;
		$diff .= 'pts.innerHTML = "(' . $sub . ')";';
	}
	if (!empty($diff)) {
?>
<script type="text/javascript">
	//<![CDATA[
		<?php echo $diff;?>
	//]]>
</script>
<?php
		flush();
	}
}

setProgress(0, _t('교정 대상을 확인하고 있습니다.'));
$items = 3 + DBQuery::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE blogid = $blogid") + DBQuery::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid");

set_time_limit(0);
$item = 0;
$corrected = 0;

setProgress($item++ / $items * 100, _t('글의 댓글 정보를 다시 계산해서 저장합니다.'));
requireComponent('Textcube.Data.Post');
Post::updateComments();

setProgress($item++ / $items * 100, _t('글의 걸린글 정보를 다시 계산해서 저장합니다.'));
requireComponent('Textcube.Data.Post');
Post::updateTrackbacks();

setProgress($item++ / $items * 100, _t('분류의 글 정보를 다시 계산해서 저장합니다.'));
requireComponent('Textcube.Data.Post');
updateEntriesOfCategory($blogid);

if ($result = DBQuery::query("SELECT id, name, parent, homepage, comment, entry, isFiltered FROM {$database['prefix']}Comments WHERE blogid = $blogid")) {
	while ($comment = mysql_fetch_assoc($result)) {
		setProgress($item++ / $items * 100, _t('댓글과 방명록 데이터를 교정하고 있습니다.'));
		$correction = '';
		if (!UTF8::validate($comment['name']))
			$correction .= ' name = \'' . mysql_tt_escape_string(UTF8::correct($comment['name'], '?')) . '\'';
		if (!UTF8::validate($comment['homepage']))
			$correction .= ' homepage = \'' . mysql_tt_escape_string(UTF8::correct($comment['homepage'], '?')) . '\'';
		if (!UTF8::validate($comment['comment']))
			$correction .= ' comment = \'' . mysql_tt_escape_string(UTF8::correct($comment['comment'], '?')) . '\'';
		if (strlen($correction) > 0) {
			DBQuery::query("UPDATE {$database['prefix']}Comments SET $correction WHERE blogid = $blogid AND id = {$comment['id']}");
			$corrected++;
		}
		if (!is_null($comment['parent']) && ($comment['isFiltered'] == 0)) {
			$r2 = DBQuery::query("SELECT id FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = {$comment['parent']} AND isFiltered = 0");
			if (mysql_num_rows($r2) <= 0) {
				trashCommentInOwner($blogid, $comment['id']);
			}
			mysql_free_result($r2);
		}
	}
	mysql_free_result($result);
}

if ($result = DBQuery::query("SELECT id, url, site, subject, excerpt FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid")) {
	while ($trackback = mysql_fetch_assoc($result)) {
		setProgress($item++ / $items * 100, _t('걸린 글 데이터를 교정하고 있습니다.'));
		$correction = '';
		if (!UTF8::validate($trackback['url']))
			$correction .= ' url = \'' . mysql_tt_escape_string(UTF8::correct($trackback['url'], '?')) . '\'';
		if (!UTF8::validate($trackback['site']))
			$correction .= ' site = \'' . mysql_tt_escape_string(UTF8::correct($trackback['site'], '?')) . '\'';
		if (!UTF8::validate($trackback['subject']))
			$correction .= ' subject = \'' . mysql_tt_escape_string(UTF8::correct($trackback['subject'], '?')) . '\'';
		if (!UTF8::validate($trackback['excerpt']))
			$correction .= ' excerpt = \'' . mysql_tt_escape_string(UTF8::correct($trackback['excerpt'], '?')) . '\'';
		if (strlen($correction) > 0) {
			DBQuery::query("UPDATE {$database['prefix']}Trackbacks SET $correction WHERE blogid = $blogid AND id = {$trackback['id']}");
			$corrected++;
		}
	}
	mysql_free_result($result);
}

setProgress(100, _t('완료되었습니다.') . "($corrected)");
finish();
?>
