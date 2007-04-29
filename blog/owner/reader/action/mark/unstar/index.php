<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'id' => array('id'),
	)
);
require ROOT . '/lib/includeForReader.php';
requireStrictRoute();
respondResultPage(markAsStar($owner, $_POST['id'], false));
?>