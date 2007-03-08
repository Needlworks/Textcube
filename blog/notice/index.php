<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
list($entries, $paging) = getEntriesWithPagingByNotice($owner, $suri['page'], $blog['entriesOnPage']);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>
