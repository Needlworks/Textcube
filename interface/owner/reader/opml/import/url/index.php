<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$VI = array(
	'POST' => array(
		'url' => array('url')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
set_time_limit(60);
$result = importOPMLFromURL($blogid, $_POST['url']);
Respond::PrintResult($result);
?>
