<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlog.php';
list($entryId) = getCommentAttributes($owner, $suri['id'], 'entry');
list($entries, $paging) = getEntryWithPaging($owner, $entryId);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
<h2><?php echo _text('답글에 답글을 작성합니다.');?></h2>
<?php
printMobileCommentFormView($suri['id']);
?>
</div>
<?php
printMobileNavigation($entry);
printMobileHtmlFooter();
?>