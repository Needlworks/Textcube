<?
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../..');
require ROOT . '/lib/include.php';
list($entries, $paging) = getEntryWithPaging($owner, $suri['id']);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader(htmlspecialchars($blog['title']));
?>
<div id="navigation">
	<!--
	<h2><?=_t('카테고리')?></h2>
	<?=getCategoriesView(getCategories($owner), true, getCategoriesSkin(), true)?>
	-->
	<h2><?=_t('최근에 달린 답글')?></h2>
	<ul>
	<?
foreach (getRecentComments($owner) as $comment) {
?>
	<li><a href="<?=$blogURL?>/comment/<?=$comment['entry']?>"><?=htmlspecialchars($comment['comment'])?></a><br/><?=htmlspecialchars($comment['name'])?> (<?=Timestamp::format2($comment['written'])?>)</li>
	<?
}
?>
	</ul>
	<h2><?=_t('최근에 달린 트랙백')?></h2>
	<ul>
	<?
foreach (getRecentTrackbacks($owner) as $trackback) {
?>
	<li><a href="<?=$blogURL?>/trackback/<?=$trackback['entry']?>"><?=htmlspecialchars($trackback['subject'])?></a><br/><?=htmlspecialchars($trackback['site'])?> (<?=Timestamp::format2($trackback['written'])?>)</li>
	<?
}
?>
	</ul>
	<!--
	<h2><?=_t('글 보관함')?></h2>
	<ul>
	<?
foreach (getArchives($owner) as $archive) {
?>
	<li><a href="<?=$blogURL?>/archive/<?=$archive['period']?>"><?=getPeriodLabel($archive['period'])?></a> (<?=$archive['count']?>)</li>
	<?
}
?>
	</ul>
	-->
</div>
<?
printMobileNavigation($entry);
printMobileHtmlFooter();
?>