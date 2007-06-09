<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'visibility' => array(array('blog', 'center', 'metapage'), 'mandatory' => false),
		'scope' => array('string', 'mandatory' => false),
		'status' => array(array('activated', 'deactivated', 'activated|deactivated'), 'mandatory' => false),
		'sort' => array(array('ascend', 'descend'), 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();

$backupScope = getUserSetting("pluginListScopeType_{$_POST['visibility']}");
$backupStatus = getUserSetting("pluginListStatusType_{$_POST['visibility']}");
$backupSort = getUserSetting('pluginListSortType');

// 하나라도 저장에 실패하면 롤백.
if (!setUserSetting("pluginListScopeType_{$_POST['visibility']}", $_POST['scope']) || !setUserSetting("pluginListStatusType_{$_POST['visibility']}", $_POST['status']) || !setUserSetting("pluginListSortType", $_POST['sort'])) {
	setUserSetting("pluginListScopeType_{$_POST['visibility']}", $backupScope);
	setUserSetting("pluginListStatusType_{$_POST['visibility']}", $backupStatus);
	setUserSetting("pluginListSortType", $backupSort);
	
	respondResultPage(1);
} else {
	respondResultPage(0);
}
?>
