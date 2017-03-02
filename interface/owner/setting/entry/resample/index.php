<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'POST' => array(
		'useResamplingAsDefault' => array('string', 'mandatory' => false),
		'useResamplingResponsive' => array('string', 'mandatory' => false)
		)
	);

require ROOT . '/library/preprocessor.php';

$isAjaxRequest = false; // checkAjaxRequest();

// 기본 설정
if (isset($_POST['useResamplingAsDefault']) && ($_POST['useResamplingAsDefault'] == "yes")) {
	Setting::setBlogSettingGlobal("resamplingDefault", "yes");
} else {
	Setting::removeBlogSettingGlobal("resamplingDefault");
}

if (isset($_POST['useResamplingResponsive']) && ($_POST['useResamplingResponsive'] == "yes")) {
	Setting::setBlogSettingGlobal("resamplingResponsive", "yes");
} else {
	Setting::removeBlogSettingGlobal("resamplingResponsive");
}

CacheControl::flushAll();

$isAjaxRequest ? Respond::PrintResult($errorResult) : header("Location: ".$_SERVER['HTTP_REFERER']);
?>
