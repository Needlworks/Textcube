<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Textcube Data Correcting</title>
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
$items = 4 + POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE blogid = $blogid") + POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid");

set_time_limit(0);
$item = 0;
$corrected = 0;

$post = new Post;

setProgress($item++ / $items * 100, _t('글의 댓글 정보를 다시 계산해서 저장합니다.'));

$post->updateComments();

setProgress($item++ / $items * 100, _t('글의 걸린글 정보를 다시 계산해서 저장합니다.'));
$post->updateRemoteResponses();

setProgress($item++ / $items * 100, _t('분류의 글 정보를 다시 계산해서 저장합니다.'));
updateEntriesOfCategory($blogid);

setProgress($item++ / $items * 100, _t('태그와 태그 관계 정보를 다시 계산해서 저장합니다.'));
$post->correctTagsAll();

if ($result = POD::query("SELECT id, name, parent, homepage, comment, entry, isfiltered FROM {$database['prefix']}Comments WHERE blogid = $blogid and isfiltered = 0")) {
	while ($comment = POD::fetch($result)) {
		setProgress($item++ / $items * 100, _t('댓글과 방명록 데이터를 교정하고 있습니다.'));
		$correction = '';
		if (!Utils_Unicode::validate($comment['name']))
			$correction .= ' name = \'' . POD::escapeString(Utils_Unicode::correct($comment['name'], '?')) . '\'';
		if (!Utils_Unicode::validate($comment['homepage']))
			$correction .= ' homepage = \'' . POD::escapeString(Utils_Unicode::correct($comment['homepage'], '?')) . '\'';
		if (!Utils_Unicode::validate($comment['comment']))
			$correction .= ' comment = \'' . POD::escapeString(Utils_Unicode::correct($comment['comment'], '?')) . '\'';
		if (strlen($correction) > 0) {
			POD::query("UPDATE {$database['prefix']}Comments SET $correction WHERE blogid = $blogid AND id = {$comment['id']}");
			$corrected++;
		}
		if (!is_null($comment['parent']) && ($comment['isfiltered'] == 0)) {
			$r2 = POD::queryCount("SELECT id FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = {$comment['parent']} AND isfiltered = 0");
			if ($r2 <= 0) {
				trashCommentInOwner($blogid, $comment['id']);
			}
		}
	}
	POD::free($result);
}

if ($result = POD::query("SELECT id, url, site, subject, excerpt FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid")) {
	while ($trackback = POD::fetch($result)) {
		setProgress($item++ / $items * 100, _t('걸린 글 데이터를 교정하고 있습니다.'));
		$correction = '';
		if (!Utils_Unicode::validate($trackback['url']))
			$correction .= ' url = \'' . POD::escapeString(Utils_Unicode::correct($trackback['url'], '?')) . '\'';
		if (!Utils_Unicode::validate($trackback['site']))
			$correction .= ' site = \'' . POD::escapeString(Utils_Unicode::correct($trackback['site'], '?')) . '\'';
		if (!Utils_Unicode::validate($trackback['subject']))
			$correction .= ' subject = \'' . POD::escapeString(Utils_Unicode::correct($trackback['subject'], '?')) . '\'';
		if (!Utils_Unicode::validate($trackback['excerpt']))
			$correction .= ' excerpt = \'' . POD::escapeString(Utils_Unicode::correct($trackback['excerpt'], '?')) . '\'';
		if (strlen($correction) > 0) {
			POD::query("UPDATE {$database['prefix']}RemoteResponses SET $correction WHERE blogid = $blogid AND id = {$trackback['id']}");
			$corrected++;
		}
	}
	POD::free($result);
}

setProgress(100, _t('완료되었습니다.') . "($corrected)");
finish();
?>
