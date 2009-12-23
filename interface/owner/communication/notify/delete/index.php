<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array('list', 'default' => '')
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.comment");
requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();
if(isset($suri['id'])) {
	if (deleteCommentNotifiedInOwner($blogid, $suri['id']) === true)
		$isAjaxRequest ? Respond::ResultPage(0) : header("Location: ".$_SERVER['HTTP_REFERER']);
	else
		$isAjaxRequest ? Respond::ResultPage(-1) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	foreach(explode(',', $_POST['targets']) as $target)
		deleteCommentNotifiedInOwner($blogid, $target, false);
	Respond::ResultPage(0);
}
?>
