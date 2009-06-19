<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireModel("blog.response.remote");


if(isset($suri['id'])) {
	$isAjaxRequest = checkAjaxRequest();
	if (deleteTrackback($blogid, $suri['id']) !== true)
		$isAjaxRequest ? Utils_Respond::ResultPage(0) : header("Location: ".$_SERVER['HTTP_REFERER']);
	else
		$isAjaxRequest ? Utils_Respond::ResultPage(-1) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	$targets = explode('~*_)', $_POST['targets']);
	foreach($targets as $target) {
		if ($target == '')
			continue;
		deleteTrackback($blogid, $target);
	}
	Utils_Respond::ResultPage(0);
}
?>
