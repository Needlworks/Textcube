<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array('list', 'default' => '')
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.response.remote");

requireStrictRoute();

if(isset($suri['id'])) {
	
	$isAjaxRequest = checkAjaxRequest();
	
	if (trashTrackback($blogid, $suri['id']) !== false)
		$isAjaxRequest ? Respond::ResultPage(0) : header("Location: ".$_SERVER['HTTP_REFERER']);
	else
		$isAjaxRequest ? Respond::ResultPage(-1) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	foreach(explode(',', $_POST['targets']) as $target)
		trashTrackback($blogid, $target);
	Respond::ResultPage(0);
}
?>
