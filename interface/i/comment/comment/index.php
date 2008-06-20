<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/includeForBlog.php';
requireView('iphoneView');
list($entryId) = getCommentAttributes($blogid, $suri['id'], 'entry');
list($entries, $paging) = getEntryWithPaging($blogid, $entryId);
$entry = $entries ? $entries[0] : null;
?>
<div id="comment_reply_<?php echo $suri['id']."_".time();?>" title="Comment reply" selected="false">
	<?php
		printIphoneCommentFormView($suri['id'], 'Write comment on comment', 'comment/comment');
	?>
	<fieldset class="navi margin-top10">
	<?php
		printIphoneNavigation($entry);
	?>
	</fieldset>
</div>