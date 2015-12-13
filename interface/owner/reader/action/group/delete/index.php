<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'id' => array('id'),
		'current' => array('int', 0)
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
$result = array('error' => deleteFeedGroup($blogid, $_POST['id']));
ob_start();
printFeedGroups($blogid, $_POST['current']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
Respond::PrintResult($result);
?>
