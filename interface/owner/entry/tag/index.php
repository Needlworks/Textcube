<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
if(count($_POST) > 0) {
	$IV = array(
		'POST' => array(
			'deleteTag' => array('id', 'mandatory' => false),
			'id' => array('id', 'mandatory' => false),
			'category' => array('id', 'mandatory' => false),
		)
	);
}

require ROOT . '/library/preprocessor.php';

requireModel('blog.tag');
requireModel('blog.entry');

if (!empty($_POST['deleteTag'])) {
	deleteTagById($blogid, $_POST['deleteTag']);
}

if (!empty($_POST['id']) && !empty($_POST['category'])) {
	$entries = array();
	foreach (getEntriesByTagId($blogid, $_POST['id']) as $entry) {
		$entries[] = $entry['id'];
	}
	changeCategoryOfEntries($blogid, implode(',', $entries), $_POST['category']);
}

$tags = getSiteTags($blogid);

require ROOT . '/interface/common/owner/header.php';
?>

<?php
require ROOT . '/interface/common/owner/footer.php';
?>
