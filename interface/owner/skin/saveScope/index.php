<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'viewtype' => array(array('listview', 'iconview'), 'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

$backupListView = getBlogSetting('skinViewType');

// 하나라도 저장에 실패하면 롤백.
if (!setBlogSetting("skinViewType", $_POST['viewtype'])) {
	setBlogSetting("skinViewType", $backupListView);
	respond::ResultPage(1);
} else {
	respond::ResultPage(0);
}
?>
