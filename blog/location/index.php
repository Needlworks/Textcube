<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
$locatives = getLocatives($blogid);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/locatives.php';
require ROOT . '/lib/piece/blog/end.php';
?>
