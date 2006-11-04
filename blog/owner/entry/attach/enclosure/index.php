<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'fileName' => array('filename'),
		'order' => array(array('0', '1'))
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
$result = setEnclosure($_POST['fileName'], $_POST['order']);
printRespond(array('error' => $result < 3 ? 0 : 1, 'order' => $result));
?>