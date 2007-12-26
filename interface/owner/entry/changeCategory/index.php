<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'targets' => array('list'),
		'category' => array('int')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
requireModel("blog.entry");

if(changeCategoryOfEntries($blogid, $_POST['targets'], $_POST['category'])) {
	respondResultPage(0);
} else {
	respondResultPage(1);
}
?>
