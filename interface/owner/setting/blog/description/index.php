<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'description' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
if (setBlogDescription($blogid, trim($_POST['description']))) {
	respondResultPage(0);
}
respondResultPage(-1);
?>
