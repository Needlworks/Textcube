<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'id' => array('id'),
		'group' => array('int')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
$result = array('error' => deleteFeed($owner, $_POST['id']));
ob_start();
printFeeds($owner, $_POST['group']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>
