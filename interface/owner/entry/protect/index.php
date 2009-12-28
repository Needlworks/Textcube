<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'password' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.entry");

requireStrictRoute();
Respond::ResultPage(protectEntry($suri['id'], isset($_POST['password']) ? $_POST['password'] : ''));
?>
