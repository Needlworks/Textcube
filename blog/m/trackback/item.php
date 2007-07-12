<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TEXTCUBE_MOBILE__', true);
define('ROOT', '../../..');
require ROOT . '/lib/includeForBlog.php';
list($entries, $paging) = getEntryWithPaging($blogid, $suri['id']);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
<?php
printMobileTrackbackView($entry['id']);
?>
</div>
<?php
printMobileNavigation($entry, true, false);
printMobileHtmlFooter();
?>