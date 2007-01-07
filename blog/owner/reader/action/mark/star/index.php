<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'id' => array('id')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
respondResultPage(markAsStar($owner, $_POST['id'], true));
?>
