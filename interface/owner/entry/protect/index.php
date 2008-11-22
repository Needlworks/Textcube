<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'password' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.entry");

requireStrictRoute();
respond::ResultPage(protectEntry($suri['id'], isset($_POST['password']) ? $_POST['password'] : ''));
?>
