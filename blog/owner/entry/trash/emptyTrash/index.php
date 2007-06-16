<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define('ROOT', '../../../../..');

$IV = array (
		'GET' => array(
			'type' => array('int'),
			'ajaxcall' => array ('any', 'mandatory' => false)
			)
		);

require ROOT . '/lib/includeForBlogOwner.php';
require("blog.trash");

requireStrictRoute();

if ($_GET['type'] == 1) {
	emptyTrash(true);
} else if ($_GET['type'] == 2) {
	emptyTrash(false);
} else {
	respondNotFoundPage();
}

if (array_key_exists('ajaxcall', $_GET)) respondResultPage(0);
else {
	if ($_GET['type'] == 1) header("Location: " . $blogURL  . '/owner/entry/trash/comment' );
	else if ($_GET['type'] == 2) header("Location: " . $blogURL  . '/owner/entry/trash/trackback' );
}
?>
