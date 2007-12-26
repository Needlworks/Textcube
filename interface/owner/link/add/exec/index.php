<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'name' => array('string'),
		'rss' => array('string', 'default' => ''),
		'url' => array('string')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.link");

requireStrictRoute();
respondResultPage(addLink($blogid, $_POST));
?>
