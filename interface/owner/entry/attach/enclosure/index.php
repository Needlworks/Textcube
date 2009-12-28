<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'fileName' => array('filename'),
		'order' => array(array('0', '1'))
	)
);
require ROOT . '/library/preprocessor.php';
requireModel('blog.attachment');
requireStrictRoute();

$result = setEnclosure($_POST['fileName'], $_POST['order']);
Respond::PrintResult(array('error' => $result < 3 ? 0 : 1, 'order' => $result));
?>
