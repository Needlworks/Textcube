<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$VI = array(
	'FILES' => array(
		'opmlFile' => array('file')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
set_time_limit(60);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head profile="http://gmpg.org/xfn/11">
		<title>OPML Uploading</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	</head>
	<body>
		<script type="text/javascript">
			//<![CDATA[
<?php
if ($xml = @file_get_contents($_FILES['opmlFile']['tmp_name'])) {
	list($status, $result) = importOPMLFromFile($blogid, $xml);
	if ($status == 0) {
		if($result['total'] == 1) {
?>
					var str = "<?php echo _f('하나의 피드를 가져왔습니다.\n피드를 업데이트 해 주십시오.', $result['total']);?>";
<?php
		} else {
?>
					var str = "<?php echo _f('%1개의 피드를 가져왔습니다.\n피드를 업데이트 해 주십시오.', $result['total']);?>";
<?php
		}
?>
					parent.Reader.refreshFeedGroup();
					parent.Reader.refreshFeedList(0);
					parent.Reader.refreshEntryList(0, 0);
					parent.Reader.refreshEntry(0, 0, 0);
					alert(str);
					<?php
	} else if ($status == 1) {
		echo 'alert("' . _t('올바른 XML 파일이 아닙니다.') . '");';
	} else if ($status == 2) {
		echo 'alert("' . _t('올바른 OPML 파일이 아닙니다.') . '");';
	}
} else
	echo 'alert("' . _t('파일 업로드에 실패했습니다.') . '");';
?>
			//]]>
		</script>
	</body>
</html>
