<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'body' => array('string')
	)
);

requireModel('blog.service');
requireStrictRoute();
	
$result = writeHtaccess($_POST['body']);
if ($result === true) {
	Respond::PrintResult(array('error' => 0));
} else {
	Respond::PrintResult(array('error' => 1, 'msg' => $result));
}
?>
