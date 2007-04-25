<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
if (file_exists(ROOT . "/cache/backup/$owner.xml")) {
	header('Content-Disposition: attachment; filename="Textcube-Backup-' . Timestamp::getDate(filemtime(ROOT . "/cache/backup/$owner.xml")) . '.xml"');
	header('Content-Description: Textcube Backup Data');
	header('Content-Transfer-Encoding: binary');
	header('Content-Type: application/xml');
	readfile(ROOT . "/cache/backup/$owner.xml");
} else {
	respondNotFoundPage();
}
?>