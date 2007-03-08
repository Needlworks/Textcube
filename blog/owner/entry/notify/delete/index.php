<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
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
	deleteCommentNotifiedInOwner($owner, $target, false);
respondResultPage(0);
?>