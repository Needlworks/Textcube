<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlogOwner.php';
if (false) {
	fetchConfigVal();
}
fireEvent($suri['directive'] . '/' . $suri['value']);
if (!headers_sent())
	respond::NotFoundPage();
?>
