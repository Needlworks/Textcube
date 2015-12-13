<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'POST' => array(
		'templateId' => array('int', 'default' => 0),
		'isSaved' => array('bool', 'default' => false),
		'entryId' => array('int', 'default' => 0)
	)
);
require ROOT . '/library/preprocessor.php';
importlib('model.blog.entry');


requireStrictRoute();

if (!is_null($entry = getEntry($blogid, $_POST['templateId']))) {
	if(!$_POST['isSaved']) {
		$entry['category'] = 0;
		$entry['visibility'] = 0;
		$entry['published'] = 'UNIX_TIMESTAMP()';
		$id = addEntry($blogid,$entry);
	} else {
		if($_POST['entryId'] == 0) Respond::ResultPage(1);
		$id = $_POST['entryId'];
	}
	// Delete original attachments.
	deleteAttachments($blogid, $id);
	if(copyAttachments($blogid, $_POST['templateId'], $id) === true) {
		$result = array("error"=>"0",
			"title"=>$entry['title'],
			"content"=>$entry['content'],
			"entryId"=>$id);
		Respond::PrintResult($result);
	}
}
Respond::ResultPage(1);
?>
