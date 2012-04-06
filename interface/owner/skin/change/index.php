<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'skinName' => array('directory' ,'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();
$result = $isAjaxRequest ? selectSkin($blogid, $_POST['skinName']) : selectSkin($blogid, $_GET['skinName']);

if ($result === true) {
	$isAjaxRequest ? Respond::PrintResult(array('error' => 0)) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	$isAjaxRequest ? Respond::PrintResult(array('error' => 1, 'msg' => _t('스킨을 변경하지 못했습니다.'))) : header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
