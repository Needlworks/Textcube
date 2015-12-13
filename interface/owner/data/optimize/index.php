<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Textcube Data Optimizing</title>
	<script type="text/javascript">
		//<![CDATA[
			var pi = window.parent.document.getElementById("optimizingIndicator");
			var pt = window.parent.document.getElementById("optimizingText");
			var pts = window.parent.document.getElementById("optimizingTextSub");
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
			alert("<?php echo _t('성공적으로 최적화 되었습니다.');?>");
<?php
	}
?>
			window.parent.document.getElementById("optimizingDataDialog").style.display = "none";
			window.parent.document.getElementById("optimizingDataDialogTitle").innerHTML = "";
			window.parent.document.getElementById("optimizingText").innerHTML = "";
			window.parent.document.getElementById("optimizingTextSub").innerHTML = "";
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

setProgress(0, _t('최적화 작업을 진행할 테이블을 확인하고 있습니다.'));
$items = 0;
set_time_limit(0);
$item = 0;
$optimized = 0;

$tcTables = getDefinedTableNames();
$tcPluginTables = getPluginTableName();
$workarounds = array_merge($tcTables, $tcPluginTables);

$items = $items + count($tcTables) + count($tcPluginTables);

foreach($workarounds as $work) {
		setProgress($item++ / ($items * 100), _f('%1 테이블을 최적화하고 있습니다.',$work));
		POD::query("OPTIMIZE TABLE {$work}");
		$optimized++;
}

setProgress(100, _t('완료되었습니다.') . "($optimized)");
finish();
?>
