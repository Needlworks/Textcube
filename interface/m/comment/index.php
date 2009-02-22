<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TEXTCUBE_MOBILE__', true);
require ROOT . '/library/includeForBlog.php';
requireView('mobileView');
list($entries, $paging) = getEntryWithPaging($blogid, $suri['id']);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
<?php
printMobileCommentView($entry['id']);
?>
</div>
<?php
printMobileNavigation($entry, false, true);
printMobileHtmlFooter();
?>
