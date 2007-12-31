<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'hp'    => array('defalut'),
		'type' => array('integer')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
$homepage = $_POST['hp'];
if ($_POST['type'] == 1 && substr($homepage,0,7) != 'http://') {
	$homepage = "http://" . $homepage;
}
if (User::setHomepage($homepage)) {
	respondResultPage(0);
}
respondResultPage( -1 );
?>
