<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'name' => array('string'),
		'rss' => array('string', 'default' => ''),
		'url' => array('string'),
		'category' => array('int','mandatory'=>false),
		'newCategory' => array('string','mandatory'=>false)
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.link");

requireStrictRoute();
respond::ResultPage(addLink($blogid, $_POST));
?>
