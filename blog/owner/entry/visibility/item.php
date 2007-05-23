<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');

$IV = array(
	'GET' => array(
		'visibility' => array('int', 0, 3, 'default' => 0),
		'command' =>  array('string', 'mandatory' => false)
		)
	);

require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();

// TeamBlog ACL check whether or not current user can edit this post.
if(empty($pc) && !empty($suri['id'])){
	$isPosting = DBQuery::queryCell("SELECT team FROM {$database['prefix']}TeamEntryRelations WHERE owner='".$owner."' and team='".$_SESSION['admin']."' and id='".$suri['id']."'" );
	if(empty($isPosting)) {
		exit;
	}
}
// End TeamBlog

//$isAjaxRequest = checkAjaxRequest();
	
if (!isset($_GET['command'])) {
	$temp = setEntryVisibility($suri['id'], isset($_GET['visibility']) ? $_GET['visibility'] : 0) == true ? 0 : 1;
	$countResult = DBQuery::queryExistence("SELECT `id` FROM `{$database['prefix']}Entries` WHERE `owner` = {$owner} AND `visibility` = 3");
	if ($countResult == false) {
		$countResult = 0;
	} else {
		$countResult = 1;
	}
	printRespond(array('error' => $temp, 'countSyndicated' => $countResult), false);
} else {
	switch ($_GET['command']) {
		case "protect":
			$_GET['command'] = 1;
			break;
		case "public":
			$_GET['command'] = 2;
			break;
		case "syndicate":
			$_GET['command'] = 3;
			break;
		case "private":
		default:
			$_GET['command'] = 0;
			break;
	}
	setEntryVisibility($suri['id'], $_GET['command']);
	header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
