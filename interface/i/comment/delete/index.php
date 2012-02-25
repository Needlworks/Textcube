<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
list($entryId) = getCommentAttributes($blogid, $suri['id'], 'entry');
printMobileHTMLHeader();
printMobileHTMLMenu('','comment');
?>

<div id="comment_<?php echo $entryId."_".$suri['id'];?>" title="Delete <?php echo $suri['id'];?>" class="panel">
<?php
if (doesHaveOwnership()) {
?>
	<h3 class="title"><?php echo _text('삭제하시겠습니까?');?></h3>
	<div class="content">
		<a data-role="button"  data-theme="b" href="<?php echo $blogURL;?>/comment/delete/action/<?php echo $suri['id'];?>"><?php echo _text('네');?></a>
		<a data-role="button"  data-theme="b" href="<?php echo $blogURL;?>/comment/<?php echo $entryId;?>"><?php echo _text('아니오');?></a>
	</div>
	<?php
} else {
?>
	<h3 class="title"><?php echo _text('작성시 입력한 비밀번호를 입력하세요');?></h3>
	<div class="content">
		<form method="post" action="<?php echo $blogURL;?>/comment/delete/action" class="dialog">
			<input type="hidden" name="replyId" value="<?php echo $suri['id'];?>" />
			<fieldset data-role="fieldcontain" class="ui-hide-label">
				<label for="password"><?php echo _text('비밀번호');?></label>
				<input type="password" name="password" id="password" placeholder="<?php echo _text('비밀번호');?>" />
				<button data-role="button"  data-theme="b" class="whiteButton margin-top10" type="submit"><?php echo _text('댓글 삭제');?></button>
				<a href="<?php echo $blogURL;?>/comment/<?php echo $entryId;?>" data-role="button" class="whiteButton"><?php echo _text('댓글 페이지로 이동');?></a>
			</fieldset>
		</form>
	</div>
<?php
}
printMobileHTMLFooter();
?>
</div>
