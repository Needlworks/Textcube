<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
if(isset($_GET['page'])) $page = $_GET['page'];
else $page = 1;
if(!empty($suri['id'])) {	// entry-related comment print
	list($entries, $paging) = getEntryWithPaging($blogid, $suri['id']);
	$entry = $entries ? $entries[0] : null;
?>
<div id="comment_<?php echo $entry['id']."_".time();?>" title="<?php echo _text('댓글');?> : <?php echo htmlspecialchars($entry['title']);?>" selected="false">
<?php
	printIphoneCommentView($entry['id']);
?>
	<fieldset class="navi margin-top10">
<?php
	printIphoneNavigation($entry, false, true);
?>
	</fieldset>
</div>
<?php

} else {	// All comments
?>
<div id="comment_<?php echo time();?>" title="<?php echo _text('최근 댓글');?>" selected="false">
<?php
	printIphoneRecentCommentView($page);
?>
	<fieldset class="navi margin-top10">
<?php
	printIphoneNavigation($entry, false, false, $paging, 'comment');
?>
	</fieldset>
</div>
<?php
}
?>
