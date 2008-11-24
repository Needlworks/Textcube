<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'id' => array('id')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
respond::ResultPage(markAsUnread($blogid, $_POST['id']));
?>
