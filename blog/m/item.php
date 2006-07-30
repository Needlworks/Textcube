<?php
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../..');
require ROOT . '/lib/include.php';
list($entries, $paging) = getEntryWithPaging($owner, $suri['id']);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
	<h2><?php echo  htmlspecialchars($entry['title'])?></h2>	
	<hr />
	<?php printMobileEntryContentView($owner, $entry, getKeywordNames($owner)); ?>
</div>
<?php
printMobileNavigation($entry, true, true, $paging);
printMobileHtmlFooter();
?>