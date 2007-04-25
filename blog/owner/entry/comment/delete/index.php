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
requireStrictRoute();
foreach(explode(',', $_POST['targets']) as $target)
	trashCommentInOwner($owner, $target, false);
respondResultPage(0);
?>
