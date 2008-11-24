<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'fileName' => array('filename'),
		'order' => array(array('0', '1'))
	)
);
require ROOT . '/library/dispatcher.php';
requireModel('blog.attachment');
requireStrictRoute();

$result = setEnclosure($_POST['fileName'], $_POST['order']);
respond::PrintResult(array('error' => $result < 3 ? 0 : 1, 'order' => $result));
?>
