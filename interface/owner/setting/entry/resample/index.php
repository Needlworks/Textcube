<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'POST' => array(
		'useResamplingAsDefault' => array('string', 'mandatory' => false)
		)
	);

require ROOT . '/lib/includeForBlogOwner.php';

$isAjaxRequest = false; // checkAjaxRequest();

// 기본 설정
if (isset($_POST['useResamplingAsDefault']) && ($_POST['useResamplingAsDefault'] == "yes")) {
	setBlogSetting("resamplingDefault", "yes");
} else {
	removeBlogSetting("resamplingDefault");
}

$isAjaxRequest ? printRespond($errorResult) : header("Location: ".$_SERVER['HTTP_REFERER']);
?>
