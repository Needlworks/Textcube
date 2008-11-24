<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'body' => array('string')
	)
);

require ROOT . '/library/dispatcher.php';
requireModel('blog.service');
requireStrictRoute();
	
$result = writeHtaccess($_POST['body']);
if ($result === true) {
	respond::PrintResult(array('error' => 0));
} else {
	respond::PrintResult(array('error' => 1, 'msg' => $result));
}
?>
