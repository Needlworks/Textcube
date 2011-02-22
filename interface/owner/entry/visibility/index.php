<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'GET' => array(
		'visibility' => array('int', 0, 3, 'default' => 0),
		'command' =>  array('string', 'mandatory' => false)
		),
	'POST' => array(
		'visibility' => array('int', 0, 3, 'default' => 0),
		'command' =>  array('string', 'mandatory' => false)
		)
	);

require ROOT . '/library/preprocessor.php';
requireModel("blog.entry");

requireStrictRoute();

// TeamBlog ACL check whether or not current user can edit this post.
if(Acl::check('group.writers') === false && !empty($suri['id'])) {
	if(getUserIdOfEntry(getBlogId(), $suri['id']) != getUserId()) { 
		@header("location:".$blogURL ."/owner/entry");
		exit;
	}
}

//$isAjaxRequest = checkAjaxRequest();
	
if (!isset($_GET['command'])) {
	$temp = setEntryVisibility($suri['id'], isset($_GET['visibility']) ? $_GET['visibility'] : 0) == true ? 0 : 1;
	$countResult = POD::queryExistence("SELECT id 
			FROM {$database['prefix']}Entries 
			WHERE blogid = ".getBlogId()." AND visibility = 3");
	if ($countResult == false) {
		$countResult = 0;
	} else {
		$countResult = 1;
	}
	Respond::PrintResult(array('error' => $temp, 'countSyndicated' => $countResult), false);
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
