<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'id' => array('id'),
		'old_group' => array('int', 0),
		'new_group' => array('int', 0),
		'url' => array('string')
	)
); 
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
$result = array('error' => editFeed($blogid, $_POST['id'], $_POST['old_group'], $_POST['new_group'], $_POST['url']));
ob_start();
printFeeds($blogid, $_POST['old_group']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
Respond::PrintResult($result);
?>
