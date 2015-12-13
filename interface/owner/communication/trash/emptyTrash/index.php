<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array (
		'GET' => array(
			'type' => array('int'),
			'ajaxcall' => array ('any', 'mandatory' => false)
			)
		);

require ROOT . '/library/preprocessor.php';
importlib("model.blog.trash");

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
	if ($_GET['type'] == 1) header("Location: " . $context->getProperty('uri.blog')  . '/owner/communication/trash/comment' );
	else if ($_GET['type'] == 2) header("Location: " . $context->getProperty('uri.blog')  . '/owner/communication/trash/trackback' );
}
?>
