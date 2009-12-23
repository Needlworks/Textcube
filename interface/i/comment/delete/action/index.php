<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
if(empty($suri['id'])) {
	$IV = array(
		'GET' => array(
			'replyId' => array('id'),
			'password' => array('string', 'mandatory' => false)
		)
	);
}
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
requireStrictRoute();

if(empty($suri['id'])) {
	list($entryId) = getCommentAttributes($blogid, $_GET['replyId'], 'entry');
	if (deleteComment($blogid, $_GET['replyId'], $entryId, isset($_GET['password']) ? $_GET['password'] : '') === false) {
		printIphoneErrorPage(_text('Comment delete error.'), _text('Incorrect Password.'), "$blogURL/comment/delete/{$_GET['replyId']}");
		exit();
	}
} else {
	list($entryId) = getCommentAttributes($blogid, $suri['id'], 'entry');
	if (deleteComment($blogid, $suri['id'], $entryId, '') === false) {
		printIphoneErrorPage(_t('Comment delete error'), _t('Administrator access only.'), "$blogURL/comment/delete/{$suri['id']}");
		exit();
	}
}
list($entries, $paging) = getEntryWithPaging($blogid, $entryId);
$entry = $entries ? $entries[0] : null;
?>
<div id="commentDeleted" title="Deleted" class="panel">
	<div class="content">
		<?php echo _t('Comment deleted.');?>
	</div>
	<a href="<?php echo "$blogURL/comment/$entryId";?>" class="whiteButton margin-top10">Go to comments page</a>
	<fieldset class="margin-top10">
		<?php
		printIphoneNavigation($entry);
		?>
	</fieldset>
</div>
