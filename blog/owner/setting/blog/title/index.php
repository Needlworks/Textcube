<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'title' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (setBlogTitle($owner, trim($_GET['title']))) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>
