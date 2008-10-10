<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'POST' => array(
		'publishWholeOnRSS' => array('int', 0, 1, 'default' => 0),
		'publishEolinSyncOnRSS' => array('int', 0, 1, 'default' => 0),
		'entriesOnRSS' => array('int', 'default' => 5),
		'commentsOnRSS' => array('int', 'default' => 5)
		)
	);
require ROOT . '/library/includeForBlogOwner.php';
requireStrictRoute();

setEntriesOnRSS($blogid, $_POST['entriesOnRSS']);
setCommentsOnRSS($blogid, $_POST['commentsOnRSS']);

// EOLIN RSS
setPublishWholeOnRSS($blogid, $_POST['publishWholeOnRSS']);
publishPostEolinSyncOnRSS($blogid, $_POST['publishEolinSyncOnRSS']);

respond::ResultPage(0);

?>
