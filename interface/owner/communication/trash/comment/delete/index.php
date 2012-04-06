<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireModel("blog.comment");


if(isset($suri['id'])) {
	$isAjaxRequest = checkAjaxRequest();
	if (deleteCommentInOwner($blogid, $suri['id']) === true)
		$isAjaxRequest ? Respond::ResultPage(0) : header("Location: ".$_SERVER['HTTP_REFERER']);
	else
		$isAjaxRequest ? Respond::ResultPage(-1) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	$targets = explode('~*_)', $_POST['targets']);
	for ($i = 0; $i < count($targets); $i++) {
		if ($targets[$i] == '')
			continue;
		deleteCommentInOwner($blogid, $targets[$i], false);
	}
	Respond::ResultPage(0);
}
?>
