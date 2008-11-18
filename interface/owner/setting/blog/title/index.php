<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'title' => array('string','default' => '')
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireStrictRoute();
if (!empty($_GET['title']) && setBlogTitle(getBlogId(), trim($_GET['title']))) {
	Respond::ResultPage(0);
}
Respond::ResultPage( - 1);
?>
