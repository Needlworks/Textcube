<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'visibility' => array(array('blog', 'center', 'coverpage'), 'mandatory' => false),
		'scope' => array('string', 'mandatory' => false),
		'status' => array(array('activated', 'deactivated', 'activated|deactivated'), 'mandatory' => false),
		'sort' => array(array('ascend', 'descend'), 'mandatory' => false),
		'viewtype' => array(array('listview', 'iconview'), 'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

$backupScope = Setting::getBlogSettingGlobal("pluginListScopeType_{$_POST['visibility']}");
$backupStatus = Setting::getBlogSettingGlobal("pluginListStatusType_{$_POST['visibility']}");
$backupSort = Setting::getBlogSettingGlobal('pluginListSortType');
$backupListView = Setting::getBlogSettingGlobal('pluginViewType');

// 하나라도 저장에 실패하면 롤백.
if (!Setting::setBlogSettingGlobal("pluginListScopeType_{$_POST['visibility']}", $_POST['scope']) || !Setting::setBlogSettingGlobal("pluginListStatusType_{$_POST['visibility']}", $_POST['status']) || !Setting::setBlogSettingGlobal("pluginListSortType", $_POST['sort']) || !Setting::setBlogSettingGlobal("pluginViewType", $_POST['viewtype'])) {
	Setting::setBlogSettingGlobal("pluginListScopeType_{$_POST['visibility']}", $backupScope);
	Setting::setBlogSettingGlobal("pluginListStatusType_{$_POST['visibility']}", $backupStatus);
	Setting::setBlogSettingGlobal("pluginListSortType", $backupSort);
	Setting::setBlogSettingGlobal("pluginViewType", $backupListView);
	Respond::ResultPage(1);
} else {
	Respond::ResultPage(0);
}
?>
