<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'targets' => array('list')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.trackback");

requireStrictRoute();
foreach(explode(',', $_POST['targets']) as $target)
	trashTrackback($blogid, $target);
respondResultPage(0);
?>
