<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array('list', 'default' => '', 'mandatory' => false),
		'ip' => array('ip', 'default' => '', 'mandatory' => false),
		'targetIPs' => array('string', 'default' => '', 'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
importlib("model.blog.comment");
requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();
if(isset($suri['id'])) {
	if (trashCommentInOwner($blogid, $suri['id']) === true)
		$isAjaxRequest ? Respond::ResultPage(0) : header("Location: ".$_SERVER['HTTP_REFERER']);
	else
		$isAjaxRequest ? Respond::ResultPage(-1) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	if(!empty($_POST['targets'])) {
		foreach(explode(',', $_POST['targets']) as $target)
			trashCommentInOwner($blogid, $target);
	}
	if(!empty($_POST['targetIPs'])) {
		$targetIPs = array_unique(explode(',', $_POST['targetIPs']));
		foreach($targetIPs as $target) {
			if (Validator::ip($target))
				trashCommentInOwnerByIP($blogid, $target);
		}
	}
	if(!empty($_POST['ip'])) {
			trashCommentInOwnerByIP($blogid, $_POST['ip']);
	}
	Respond::ResultPage(0);
}
?>
