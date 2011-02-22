<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array (
		'GET' => array(
			'type' => array('int'),
			'ajaxcall' => array ('any', 'mandatory' => false)
			)
		);

require ROOT . '/library/preprocessor.php';
requireModel("blog.trash");

requireStrictRoute();

if ($_GET['type'] == 1) {
	emptyTrash(true);
} else if ($_GET['type'] == 2) {
	emptyTrash(false);
} else {
	Respond::NotFoundPage();
}

if (array_key_exists('ajaxcall', $_GET)) Respond::ResultPage(0);
else {
	if ($_GET['type'] == 1) header("Location: " . $blogURL  . '/owner/communication/trash/comment' );
	else if ($_GET['type'] == 2) header("Location: " . $blogURL  . '/owner/communication/trash/trackback' );
}
?>
