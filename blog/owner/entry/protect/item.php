<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'password' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
respondResultPage(protectEntry($suri['id'], isset($_POST['password']) ? $_POST['password'] : ''));
?>