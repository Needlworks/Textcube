<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
if(!empty($suri['value'])) {
	$suri['page'] = getGuestbookPageById($blogid,$suri['value']);
}
list($comments, $paging) = getCommentsWithPagingForGuestbook($blogid, $suri['page'], $skinSetting['commentsOnGuestbook']);
notifyComment();
require ROOT . '/library/piece/blog/begin.php';
require ROOT . '/library/piece/blog/guestbook.php';
require ROOT . '/library/piece/blog/end.php';
?>
