<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'description' => array('string', 'default' => '')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if (setBlogDescription($blogid, trim($_POST['description']))) {
	Respond::ResultPage(0);
}
Respond::ResultPage(-1);
?>
