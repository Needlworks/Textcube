<?php
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'module' => array('string'),
		'direction' => array('string'),
		'type' => array("string")
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

if ($_POST['type'] == "sidebar-basic")
	$_POST['module'] = "%{$_POST['module']}%";
$sidebarOrder = explode("|", getUserSetting('sidebarOrder'));
$sidebarOrderFlip = array_flip($sidebarOrder);
$currentPosition = $sidebarOrderFlip[$_POST['module']];

if ($_POST['direction'] == "up") {
	if (isset($sidebarOrder[$currentPosition - 1])) {
		$temp = $sidebarOrder[$currentPosition - 1];
		$sidebarOrder[$currentPosition - 1] = $sidebarOrder[$currentPosition];
		$sidebarOrder[$currentPosition] = $temp;
	} else {
		printRespond(array('error' => 1, 'msg' => _t('더 이상 이동할 수 없습니다.')));
	}
} else if ($_POST['direction'] == "down") {
	if (isset($sidebarOrder[$currentPosition + 1])) {
		printRespond(array('error' => 1, 'msg' => $_POST['module']));
		$temp = $sidebarOrder[$currentPosition + 1];
		$sidebarOrder[$currentPosition + 1] = $sidebarOrder[$currentPosition];
		$sidebarOrder[$currentPosition] = $temp;
	} else {
		printRespond(array('error' => 1, 'msg' => _t('더 이상 이동할 수 없습니다.')));
	}
}

setUserSetting('sidebarOrder', implode("|", $sidebarOrder));
respondResultPage(0);
?>
