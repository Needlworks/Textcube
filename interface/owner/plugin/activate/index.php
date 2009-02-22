<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'name' => array('directory', 'default' => null)
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if (!empty($_POST['name']) && activatePlugin($_POST['name']))
	respond::ResultPage(0);
respond::ResultPage(1);
?>
