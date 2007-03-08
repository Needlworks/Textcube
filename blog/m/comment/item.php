<?php
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../..');
require ROOT . '/lib/includeForBlog.php';
list($entries, $paging) = getEntryWithPaging($owner, $suri['id']);
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