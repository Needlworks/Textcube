<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
$locatives = getLocatives($blogid);
require ROOT . '/library/piece/blog/begin.php';
require ROOT . '/library/piece/blog/locatives.php';
require ROOT . '/library/piece/blog/end.php';
?>
