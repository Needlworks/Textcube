<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
if(!empty($suri['value'])) {
	$suri['page'] = getGuestbookPageById($blogid,$suri['value']);
}
list($comments, $paging) = getCommentsWithPagingForGuestbook($blogid, $suri['page'], $skinSetting['commentsOnGuestbook']);
notifyComment();
require ROOT . '/interface/common/blog/begin.php';
require ROOT . '/interface/common/blog/guestbook.php';
require ROOT . '/interface/common/blog/end.php';
?>
