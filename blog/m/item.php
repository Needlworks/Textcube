<?
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../..');
require ROOT . '/lib/include.php';
list($entries, $paging) = getEntryWithPaging($owner, $suri['id']);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
	<h2><?=htmlspecialchars($entry['title'])?></h2>	
	<hr />
	<? printMobileEntryContentView($owner, $entry, getKeywordNames($owner)); ?>
</div>
<?
printMobileNavigation($entry, true, true, $paging);
printMobileHtmlFooter();
?>