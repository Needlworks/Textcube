<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_MOBILE__', true);
require ROOT . '/library/preprocessor.php';
requireView('mobileView');
list($entryId) = getCommentAttributes($blogid, $suri['id'], 'entry');
list($entries, $paging) = getEntryWithPaging($blogid, $entryId);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
<h2><?php echo _text('댓글에 댓글을 작성합니다.');?></h2>
<?php
printMobileCommentFormView($suri['id']);
?>
</div>
<?php
printMobileNavigation($entry);
printMobileHtmlFooter();
?>
