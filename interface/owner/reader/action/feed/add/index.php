<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'group' => array('int'),
		'url' => array('url')
	) 
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

if(strpos($_POST['url'],'http://') !== 0) $_POST['url'] = 'http://'.$_POST['url'];
$result = array('error' => addFeed(getBlogId(), $_POST['group'], $_POST['url']));
ob_start();
printFeeds($blogid, $_POST['group']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
Respond::PrintResult($result);
?>
