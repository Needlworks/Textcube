<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title>Tattertools Data Correcting</title>
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
			alert("<?php echo  $error?>");
<?php
	} else {
?>
			alert("<?php echo  _t('성공적으로 교정되었습니다.')?>");
<?php
	}
?>
			window.parent.document.getElementById("correctingDataDialog").style.display = "none";
			window.parent.document.getElementById("correctingDataDialogTitle").innerHTML = "";
			window.parent.document.getElementById("correctingText").innerHTML = "";
			window.parent.document.getElementById("correctingTextSub").innerHTML = "";
		//]]>
	</script>
	<?php echo  _t('완료.')?>
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
		<?php echo  $diff?>
	//]]>
</script>
<?php
		flush();
	}
}

setProgress(0, _t('교정 대상을 확인하고 있습니다.'));
$items = 3 + DBQuery::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE owner = $owner") + DBQuery::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Trackbacks WHERE owner = $owner");

set_time_limit(0);
$item = 0;
$corrected = 0;

setProgress($item++ / $items * 100, _t('글의 댓글 정보를 다시 계산해서 저장합니다.'));
requireComponent('Tattertools.Data.Post');
Post::updateComments();

setProgress($item++ / $items * 100, _t('글의 트랙백 정보를 다시 계산해서 저장합니다.'));
requireComponent('Tattertools.Data.Post');
Post::updateTrackbacks();

setProgress($item++ / $items * 100, _t('분류의 글 정보를 다시 계산해서 저장합니다.'));
requireComponent('Tattertools.Data.Post');
updateEntriesOfCategory($owner);

if ($result = mysql_query("SELECT id, name, homepage, comment FROM {$database['prefix']}Comments WHERE owner = $owner")) {
	while ($comment = mysql_fetch_assoc($result)) {
		setProgress($item++ / $items * 100, _t('댓글과 방명록 데이터를 교정하고 있습니다.'));
		$correction = '';
		if (!UTF8::validate($comment['name']))
			$correction .= ' name = \'' . mysql_escape_string(UTF8::correct($comment['name'], '?')) . '\'';
		if (!UTF8::validate($comment['homepage']))
			$correction .= ' homepage = \'' . mysql_escape_string(UTF8::correct($comment['homepage'], '?')) . '\'';
		if (!UTF8::validate($comment['comment']))
			$correction .= ' comment = \'' . mysql_escape_string(UTF8::correct($comment['comment'], '?')) . '\'';
		if (strlen($correction) > 0) {
			mysql_query("UPDATE {$database['prefix']}Comments SET $correction WHERE owner = $owner AND id = {$comment['id']}");
			$corrected++;
		}
	}
	mysql_free_result($result);
}

if ($result = mysql_query("SELECT id, url, site, subject, excerpt FROM {$database['prefix']}Trackbacks WHERE owner = $owner")) {
	while ($trackback = mysql_fetch_assoc($result)) {
		setProgress($item++ / $items * 100, _t('트랙백 데이터를 교정하고 있습니다.'));
		$correction = '';
		if (!UTF8::validate($trackback['url']))
			$correction .= ' url = \'' . mysql_escape_string(UTF8::correct($trackback['url'], '?')) . '\'';
		if (!UTF8::validate($trackback['site']))
			$correction .= ' site = \'' . mysql_escape_string(UTF8::correct($trackback['site'], '?')) . '\'';
		if (!UTF8::validate($trackback['subject']))
			$correction .= ' subject = \'' . mysql_escape_string(UTF8::correct($trackback['subject'], '?')) . '\'';
		if (!UTF8::validate($trackback['excerpt']))
			$correction .= ' excerpt = \'' . mysql_escape_string(UTF8::correct($trackback['excerpt'], '?')) . '\'';
		if (strlen($correction) > 0) {
			mysql_query("UPDATE {$database['prefix']}Trackbacks SET $correction WHERE owner = $owner AND id = {$trackback['id']}");
			$corrected++;
		}
	}
	mysql_free_result($result);
}

setProgress(100, _t('완료되었습니다.') . "($corrected)");
finish();
?>
