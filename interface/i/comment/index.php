<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/includeForBlog.php';
requireView('iphoneView');
list($entries, $paging) = getEntryWithPaging($blogid, $suri['id']);
$entry = $entries ? $entries[0] : null;
?>
<div id="comment_<?php echo $entry['id']."_".time();?>" title="Comment <?php echo $entry['id'];?>" selected="false">
	<?php
		printIphoneCommentView($entry['id']);
	?>
	<fieldset class="navi margin-top10">
	<?php
		printIphoneNavigation($entry, false, true);
	?>
	</fieldset>
</div>
