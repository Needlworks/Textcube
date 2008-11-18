<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'group' => array('int'),
		'url' => array('url')
	) 
);
require ROOT . '/library/includeForReader.php';
requireStrictRoute();
$result = array('error' => addFeed(getBlogId(), $_POST['group'], $_POST['url']));
ob_start();
printFeeds($blogid, $_POST['group']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
Respond::PrintResult($result);
?>
