<?
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForOwner.php';
set_time_limit(60);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head profile="http://gmpg.org/xfn/11">
		<title>OPML Uploading</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<script type="text/javascript">
		</script>
	</head>
	<body>
		<script type="text/javascript">
		<?
if ($xml = @file_get_contents($_FILES['opmlFile']['tmp_name'])) {
	list($status, $result) = importOPMLFromFile($owner, $xml);
	if ($status == 0) {
?>
					var str = "<?=_f('%1개의 피드를 가져왔습니다.\n피드를 업데이트 해주세요.', $result['total'])?>";
					parent.Reader.refreshFeedGroup();
					parent.Reader.refreshFeedList(0);
					parent.Reader.refreshEntryList(0, 0);
					parent.Reader.refreshEntry(0, 0, 0);
					alert(str);
					<?
	} else if ($status == 1) {
		echo 'alert("' . _t('올바른 XML 파일이 아닙니다.') . '");';
	} else if ($status == 2) {
		echo 'alert("' . _t('올바른 OPML 파일이 아닙니다.') . '");';
	}
} else
	echo 'alert("' . _t('파일 업로드에 실패했습니다.') . '");';
?>
		</script>
	</body>
</html>
