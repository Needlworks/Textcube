<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

require ROOT . '/lib/includeForIcon.php';

$icon_path = ROOT . "/attach/$blogid/favicon.ico";
if( !file_exists($icon_path) ) {
	$icon_path = ROOT . '/image/icon_favicon_default.ico';
}

dumpWithEtag( $icon_path );
?>
