<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'description' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (setBlogDescription($owner, trim($_POST['description']))) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>
