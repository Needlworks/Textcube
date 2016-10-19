<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
if (file_exists(__TEXTCUBE_CACHE_DIR__."/backup/$blogid.xml")) {
	header('Content-Disposition: attachment; filename="Textcube-Backup-' . Timestamp::getDate(filemtime(__TEXTCUBE_CACHE_DIR__."/backup/$blogid.xml")) . '.xml"');
	header('Content-Description: Textcube Backup Data');
	header('Content-Transfer-Encoding: binary');
	header('Content-Type: application/xml');
	readfile(__TEXTCUBE_CACHE_DIR__."/backup/$blogid.xml");
} else {
	Respond::NotFoundPage();
}
?>
