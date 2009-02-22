<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array('list', 'default' => '')
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.comment");
requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();
if(isset($suri['id'])) {
	if (trashCommentInOwner($blogid, $suri['id']) === true)
		$isAjaxRequest ? respond::ResultPage(0) : header("Location: ".$_SERVER['HTTP_REFERER']);
	else
		$isAjaxRequest ? respond::ResultPage(-1) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	foreach(explode(',', $_POST['targets']) as $target)
		trashCommentInOwner($blogid, $target);
	respond::ResultPage(0);
}
?>
