<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array('list'),
		'category' => array('int')
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireStrictRoute();
requireModel("blog.entry");


if(changeCategoryOfEntries($blogid, $_POST['targets'], $_POST['category'])) {
	respond::ResultPage(0);
} else {
	respond::ResultPage(1);
}
?>
