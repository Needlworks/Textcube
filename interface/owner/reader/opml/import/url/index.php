<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$VI = array(
	'POST' => array(
		'url' => array('url')
	)
);
require ROOT . '/library/includeForReader.php';
requireStrictRoute();
set_time_limit(60);
$result = importOPMLFromURL($blogid, $_POST['url']);
respond::PrintResult($result);
?>
