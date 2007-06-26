<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'title' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
if (setBlogTitle(getBlogId(), trim($_GET['title']))) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>
