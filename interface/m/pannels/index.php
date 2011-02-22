<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_MOBILE__', true);
require ROOT . '/library/preprocessor.php';
requireView('mobileView');
list($entries, $paging) = getEntryWithPaging($blogid, $suri['id']);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader(htmlspecialchars($blog['title']));
?>
<div id="pannels">
	<!--
	<h2><?php echo _t('카테고리');?></h2>
	<?php echo getCategoriesView(getEntriesTotalCount($blogid), getCategories($blogid), true, true);?>
	-->
	<h2><?php echo _text('최근에 달린 댓글');?></h2>
	<?php
		$comments = getRecentComments($blogid);
		if(count($comments) > 0)
			echo '<ul>';
		foreach ($comments as $comment) {
		?>
			<li><a href="<?php echo $blogURL;?>/comment/<?php echo $comment['entry'];?>"><?php echo htmlspecialchars($comment['comment']);?></a><br /><?php echo htmlspecialchars($comment['name']);?> (<?php echo Timestamp::format2($comment['written']);?>)</li>
		<?php
		}
		if(count($comments) > 0)
			echo '</ul>';
		?>
	<h2><?php echo _text('최근에 걸린글');?></h2>
	<?php
		$trackbacks = getRecentTrackbacks($blogid);
		if(count($trackbacks) > 0)
			echo '<ul>';
		foreach ($trackbacks as $trackback) {
		?>
			<li><a href="<?php echo $blogURL;?>/trackback/<?php echo $trackback['entry'];?>"><?php echo htmlspecialchars($trackback['subject']);?></a><br /><?php echo htmlspecialchars($trackback['site']);?> (<?php echo Timestamp::format2($trackback['written']);?>)</li>
		<?php
		}
		if(count($trackbacks) > 0)
			echo '</ul>';
	?>
	<!--
	<h2><?php echo _text('글 보관함');?></h2>
	<ul>
	<?php
foreach (getArchives($blogid) as $archive) {
?>
	<li><a href="<?php echo $blogURL;?>/archive/<?php echo $archive['period'];?>"><?php echo getPeriodLabel($archive['period']);?></a> (<?php echo $archive['count'];?>)</li>
	<?php
}
?>
	</ul>
	-->
</div>
<?php
printMobileNavigation($entry);
printMobileHtmlFooter();
?>
