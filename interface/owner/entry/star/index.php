<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'GET' => array(
		'mark' => array('int', 0, 2, 'default' => 0),
		'command' =>  array('string', 'mandatory' => false)
		),
	'POST' => array(
		'mark' => array('int', 0, 2, 'default' => 0),
		'command' =>  array('string', 'mandatory' => false)
		)
	);

require ROOT . '/library/preprocessor.php';
importlib("model.blog.entry");

requireStrictRoute();

// TeamBlog ACL check whether or not current user can edit this post.
if(Acl::check('group.writers') === false && !empty($suri['id'])) {
	if(getUserIdOfEntry(getBlogId(), $suri['id']) != getUserId()) { 
		@header("location:".$context->getProperty('uri.blog') ."/owner/entry");
		exit;
	}
}

//$isAjaxRequest = checkAjaxRequest();
	
if (!isset($_GET['command'])) {
	$temp = setEntryStar($suri['id'], isset($_GET['mark']) ? $_GET['mark'] : 1) == true ? 0 : 1;
	$countResult = POD::queryExistence("SELECT id 
			FROM {$database['prefix']}Entries 
			WHERE blogid = ".getBlogId()." AND starred = ".$_GET['mark']);
	if ($countResult == false) {
		$countResult = 0;
	} else {
		$countResult = 1;
		fireEvent('ChangeStarred', $_GET['mark'], $suri['id']);
	}
	Respond::PrintResult(array('error' => $temp), false);
} else {
	switch ($_GET['command']) {
		case "unmark":
			$_GET['command'] = 1;
			break;
		case "mark":
			$_GET['command'] = 2;
			break;
		default:
			$_GET['command'] = 0;
			break;
	}
	setEntryStar($suri['id'], $_GET['command']);
	header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
