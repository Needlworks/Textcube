<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
if (file_exists(ROOT . "/cache/backup/$blogid.xml")) {
	header('Content-Disposition: attachment; filename="Textcube-Backup-' . Timestamp::getDate(filemtime(ROOT . "/cache/backup/$blogid.xml")) . '.xml"');
	header('Content-Description: Textcube Backup Data');
	header('Content-Transfer-Encoding: binary');
	header('Content-Type: application/xml');
	readfile(ROOT . "/cache/backup/$blogid.xml");
} else {
	respond::NotFoundPage();
}
?>
