<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_MOBILE__', true);
require ROOT . '/library/preprocessor.php';
requireView('mobileView');
list($entryId) = getCommentAttributes($blogid, $suri['id'], 'entry');
printMobileHtmlHeader();
?>
<div id="content">
	<?php
if (doesHaveOwnership()) {
?>
	<h2><?php echo _text('삭제하시겠습니까?');?></h2>
	<div class="content">
		<a href="<?php echo $blogURL;?>/comment/delete/action/<?php echo $suri['id'];?>"><?php echo _text('예');?></a>
		<a href="<?php echo $blogURL;?>/comment/<?php echo $entryId;?>"><?php echo _text('아니요');?></a>
	</div>
	<?php
} else {
?>
	<h2><?php echo _text('비밀번호를 입력해 주십시오.');?></h2>
	<div class="content">
		<form method="post" action="<?php echo $blogURL;?>/comment/delete/action">
		<fieldset>
		<input type="hidden" name="replyId" value="<?php echo $suri['id'];?>" />
		<input type="password" name="password" id="password" />
		<input type="submit" value="<?php echo _text('삭제');?>" />
		</fieldset>
		</form>
		<a href="<?php echo $blogURL;?>/comment/<?php echo $entryId;?>"><?php echo _text('댓글 보기 화면으로');?></a>
	</div>
	<?php
}
?>
</div>
<?php
printMobileHtmlFooter();
?>
