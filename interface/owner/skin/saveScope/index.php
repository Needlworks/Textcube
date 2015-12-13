<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'viewtype' => array(array('listview', 'iconview'), 'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

$backupListView = Setting::getBlogSettingGlobal('skinViewType');

// 하나라도 저장에 실패하면 롤백.
if (!Setting::setBlogSettingGlobal("skinViewType", $_POST['viewtype'])) {
	Setting::setBlogSettingGlobal("skinViewType", $backupListView);
	Respond::ResultPage(1);
} else {
	Respond::ResultPage(0);
}
?>
