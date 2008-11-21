<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

if (false) {
	fetchConfigVal();
}
list($status, $url) = updateRandomFeed();
Respond::PrintResult(array('error' => $status, 'url' => $url));
?>
