<?
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../../..');
require ROOT . '/lib/include.php';
list($entryId) = getCommentAttributes($owner, $suri['id'], 'entry');
printMobileHtmlHeader();
?>
<div id="content">
	<?
if (doesHaveOwnership()) {
?>
	<h2><?=_t('삭제하시겠습니까?')?></h2>
	<div class="content">
		<a href="<?=$blogURL?>/comment/delete/action/<?=$suri['id']?>"><?=_t('예')?></a>
		<a href="<?=$blogURL?>/comment/<?=$entryId?>"><?=_t('아니요')?></a>
	</div>
	<?
} else {
?>
	<h2><?=_t('비밀번호를 입력해주세요.')?></h2>
	<div class="content">
		<form method="post" action="<?=$blogURL?>/comment/delete/action">
		<fieldset>
		<input type="hidden" name="replyId" value="<?=$suri['id']?>" />
		<input type="password" name="password" id="password" />
		<input type="submit" value="<?=_t('삭제')?>" />
		</fieldset>
		</form>
		<a href="<?=$blogURL?>/comment/<?=$entryId?>"><?=_t('답글보기 화면으로')?></a>
	</div>
	<?
}
?>
</div>
<?
printMobileHtmlFooter();
?>