<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array('list'),
		'category' => array('int')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
importlib("model.blog.entry");


if(changeCategoryOfEntries($blogid, $_POST['targets'], $_POST['category'])) {
	Respond::ResultPage(0);
} else {
	Respond::ResultPage(1);
}
?>
