<?
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../../..');
require ROOT . '/lib/include.php';
list($entryId) = getCommentAttributes($owner, $suri['id'], 'entry');
list($entries, $paging) = getEntryWithPaging($owner, $entryId);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
<h2><?=_text('답글에 답글을 작성합니다.')?></h2>
<?
printMobileCommentFormView($suri['id']);
?>
</div>
<?
printMobileNavigation($entry);
printMobileHtmlFooter();
?>