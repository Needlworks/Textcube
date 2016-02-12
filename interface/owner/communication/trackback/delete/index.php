<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array('list', 'default' => '', 'mandatory' => false),
		'targetIPs' => array('string', 'default' => '', 'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
importlib("model.blog.remoteresponse");

requireStrictRoute();

if(isset($suri['id'])) {

	$isAjaxRequest = checkAjaxRequest();

	if (trashTrackback($blogid, $suri['id']) !== false)
		$isAjaxRequest ? Respond::ResultPage(0) : header("Location: ".$_SERVER['HTTP_REFERER']);
	else
		$isAjaxRequest ? Respond::ResultPage(-1) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else if (!empty($_POST['targets'])) {
	foreach(explode(',', $_POST['targets']) as $target)
		trashTrackback($blogid, $target);
	if (!empty($_POST['targetIPs'])) {
		foreach (explode(',', $_POST['targetIPs']) as $ip)
			trashRemoteResponsesByIP($blogid, $ip);
	}
	Respond::ResultPage(0);
}
Respond::ResultPage(-1);
?>
