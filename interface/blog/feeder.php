<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/preprocessor.php';

if (false) {
	fetchConfigVal();
}
list($status, $url) = updateRandomFeed();
respond::PrintResult(array('error' => $status, 'url' => $url));
?>
